//	sses_ps / plotsvys.c
//
//	Version 2.4.2
//	April 20, 2012
//
//	Modified by :  C. Bergman
//	Purpose:       To display User-select5ed colors on survey graphs.

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <math.h>
#include <libgen.h>
#include <sys/stat.h>

// #define USEKEYLOK 1
#ifdef USEKEYLOK
#include "../../vendor/keylok/linux/keylok.h"
#endif
#include "gnuplot_i.h"
#include "dbio.h"

int bNoData=0;
int bUseLogScale=0;
float cutoffDepth1=-9999.0;
float cutoffDepth2=-9999.0;
float yscale1, yscale2;
float yfactor=.10;
float propazm=0.0;

char transparent[128];
int fontsize=8;
int anno = 0;
float height, width;
float forcedheight, forcedwidth;
float forcedmintvd, forcedmaxtvd;
float forcedminvs, forcedmaxvs;
float elev_ground, elev_rkb;

float labeling_start=0;
int label_every=1;
int label_dmd =0;
int label_dvs =0;
int label_dreport=0;
int label_orient=0;
int label_dwebplot=0;
float plotbias=0;
float plotscale=1;
float scaleright=500;
long int tableid=0;
float startdepth, enddepth;
float cutoffAngle=-1.0;
float minsvydepth, maxsvydepth;
float minmd, maxmd;
float mintvd, minvs;
float maxtvd, maxvs;
float minns, minew;
float maxns, maxew;
float mindataval=99999.0;
float maxdataval=-99999.0;
float bitvs, bittvd;
float bittot, bitbot;

FILE *outfile;
FILE *projFile;
FILE *annoFile;
FILE *cpFile;
char cmdstr[8192];
FILE *totFile,*botFile;
float lastPrjTot=0.0;
float lastPrjBot=0.0;
float lastSvyTot=0.0;
float lastSvyBot=1.0;
float controlTot=0.0;
float controlBot=1.0;
int vslon = 0;
float vsland = 0.0;
float vsldip = 0.0;

#define MAX_ADDFORMS 32
typedef struct t_addforms T_ADDFORMS;
struct t_addforms {
	unsigned long id;
	char label[4095];
	char color[128];
	char tot_filename[L_tmpnam];
	char bot_filename[L_tmpnam];
	FILE *totFile;
	FILE *botFile;
	float starttot, endtot;
	float startbot, endbot;
	float thickness, offset;
	float vs, fault;
};
T_ADDFORMS	addforms[MAX_ADDFORMS];
T_ADDFORMS  addformsproj[MAX_ADDFORMS];
ResultSet resultSetAddForms;
ResultSet *res_setAddForms = &resultSetAddForms;
ResultSet resultSetAddFormsProj;
ResultSet *res_setAddFormsProj = &resultSetAddFormsProj;

char clfn[L_tmpnam];
char svyfn[L_tmpnam];
char wpfn[L_tmpnam];
char gdfn1[L_tmpnam];
char gdfn2[L_tmpnam];
char projfn[L_tmpnam];
char annofn[L_tmpnam];
char cpfn[L_tmpnam];
char totfn[L_tmpnam];
char botfn[L_tmpnam];
char totfn2[L_tmpnam];
char botfn2[L_tmpnam];

// start of variables used in filled curves

char filldat[256][256]; // array of values that will go into GNUplot file
char bg_color[MAX_ADDFORMS][256];
char bg_percent[MAX_ADDFORMS][256];
char pat_color[MAX_ADDFORMS][256];
char pat_num[MAX_ADDFORMS][256];
char show_line[MAX_ADDFORMS][16];
char tmpstr[4095],tstr[256];
char filldfn[L_tmpnam]; // file name of new GNUplot file
FILE *fdFile; // file descriptor of new GNUplot file
int fdi,fdi_start=0,fdfirst;
int numforms; // number of formations

// end of variables used in filled curves

#define L_COLORSTR 32
char plotcolortot[L_COLORSTR];
char plotcolorbot[L_COLORSTR];
char plotcolorwp[L_COLORSTR];

char outFilename[4095];
char outFilename2[4095];
char outFilename3[4095];
char outFilename4[4095];
#define MAX_TARGETS 32
char targetfn[MAX_TARGETS][1024];
FILE *targetFile[MAX_TARGETS];

char dbname[4065];
int svycnt=0;
int setcoms=0;

#define MAXPLOTS 32
char plotstr[MAXPLOTS][1024];
char setstr[MAXPLOTS][1024];

gnuplot_ctrl *gplot;

int plotType;
#define PLOTTYPE_VS	1
#define PLOTTYPE_HOR 2
#define PLOTTYPE_LAT 3
#define PLOTTYPE_POL 4

int avgsz;
float projlen;

float softwareVersion = 1.0;
int	keycheckOK = 0;
unsigned char softwareOptions = 0;
unsigned int keySerialNumber = 0;

/*****************************************************************************/

#ifdef USEKEYLOK
void CheckForValidKey(void) {
	//fprintf(stderr, "Checking for security key...");
	keycheckOK = CheckForSecurityKeyAccess();
	//fprintf(stderr, "%s\n", GetSecurityKeyResult());

	if(keycheckOK) {
		softwareVersion = (float)GetSecurityKeyVersionMajor();
		softwareVersion += (float)GetSecurityKeyVersionMinor() / 100.0f;
		sprintf(cmdstr, "Software version: %.2f", softwareVersion);
		//fprintf(stderr, "%s\n", cmdstr);

		softwareOptions = GetSecurityKeyOptionFlags();
		//fprintf(stderr, "%s\n", GetSecurityKeyResult());
		keySerialNumber = GetSecurityKeySerialNumber();
		//fprintf(stderr, "%s\n", GetSecurityKeyResult());
	}
}
#endif

/*****************************************************************************/

void readAppinfo(char *progname) {
	if (DoQuery(res_set2, "SELECT * FROM appinfo;")) {
		fprintf(stderr, "%s: readAppInfo: Error in select query\n", progname);
		CloseDb();
		exit (-1);
	}
	if(FetchRow(res_set2)) {
		plotbias=atof(FetchField(res_set2, "bias"));
		plotscale=atof(FetchField(res_set2, "scale"));
		scaleright=atof(FetchField(res_set2, "scaleright"));
		tableid=atol(FetchField(res_set2, "dataset"));
		bUseLogScale=atol(FetchField(res_set2, "uselogscale"));
		labeling_start=atof(FetchField(res_set2, "labeling_start"));
		label_every =atoi(FetchField(res_set2, "label_every"));
		label_dmd=atoi(FetchField(res_set2, "label_dmd"));
		label_dvs=atoi(FetchField(res_set2, "label_dvs"));
		label_dreport=atoi(FetchField(res_set2, "label_dreport"));
		label_orient=atoi(FetchField(res_set2, "label_orient"));
		label_dwebplot=atoi(FetchField(res_set2, "label_dwebplot"));
	}
	FreeResult(res_set2);
}

/*****************************************************************************/

double degrees(double aRadians)
{
	return (180.0 / M_PI * aRadians);
}

/*****************************************************************************/

double radians(double aDegrees)
{
	return (M_PI / 180.0 * aDegrees);
}

/*****************************************************************************/

double unsignedAngle(double angle)
{
	double a = angle;
	if (a < 0.0) return (a + 360.0);
	if (a >= 360.0) return (a - 360.0);
	return a;
}

/*****************************************************************************/

void readWellinfo(char *progname) {
	if (DoQuery(res_set, "SELECT * FROM wellinfo")) {
		printf("%s: readWellinfo: Error in select query\n", progname);
		return;
	}
	if(FetchRow(res_set))
	{
		propazm = radians(atof(FetchField(res_set, "propazm")));
		elev_ground = atof(FetchField(res_set, "elev_ground"));
		elev_rkb = atof(FetchField(res_set, "elev_rkb"));
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void rotate(float *ns, float *ew, float angle) {
	float fns=*ns;
	float few=*ew;
	float a = radians(90.0)-angle;

	*ew=(few*cos(a))-(-fns*sin(a));
	*ns=(fns*cos(a))-(few*sin(a));
}

/*****************************************************************************/
void buildAdditionalFormationFilesProj(void) {
	int linestyle=90;
	int i=0;
	float vs, tot, fault;
	float lastvs=0.0, lasttot=0.0, lastfault=0.0;
	float firstvs, firsttot, rotangle, thick;
	int first;
	char query[256];
	//fprintf(stderr, "13\n");
	if (DoQuery(res_setAddFormsProj, "select * from addforms order by thickness")) {
		fprintf(stderr, "initAdditionalFormations: Error in select query\n");
		CloseDb();
		exit (-1);
	}
	//fprintf(stderr, "14\n");
	i=0;
	while(FetchRow(res_setAddFormsProj))
	{
		addformsproj[i].id=atol(FetchField(res_setAddFormsProj, "id"));
		strcpy( addformsproj[i].color, FetchField(res_setAddFormsProj, "color") );
		strcpy( addformsproj[i].label, FetchField(res_setAddFormsProj, "label") );
		strcpy( addformsproj[i].tot_filename, tmpnam(NULL));
		addformsproj[i].totFile=fopen(addformsproj[i].tot_filename, "a+");
		// here is where we define the linewidth of the forecast
		gnuplot_cmd(gplot, "set style line %d lt 1 lc rgb '#%s' lw 3 pt 3 ps .6", linestyle,"d00000");
		linestyle++;
		i++;
	}
	FreeResult(res_setAddFormsProj);

	if(forcedminvs<99900.0 && forcedminvs>-99900.0)	minvs=forcedminvs;

	// go through all the formations

	fdfirst=1;
	fdi_start = fdi; // the start now is where it fdi last ended
	for(i=0; addformsproj[i].totFile!=NULL; i++) {

		// collect the last surveyed data (projection ID of -1)

		first=0;
		sprintf(query, "select * from addformsdata where infoid=%ld and projid=-1 order by md desc limit 1", addformsproj[i].id);
		if (DoQuery(res_setAddFormsProj, query)) {
			fprintf(stderr, "initAdditionalFormations: Error in select query\n");
			CloseDb();
			exit (-1);
		}

		while(FetchRow(res_setAddFormsProj)) {

			vs=atof(FetchField(res_setAddFormsProj, "vs"));

			tot=atof(FetchField(res_setAddFormsProj, "tot"));

			fault=atof(FetchField(res_setAddFormsProj, "fault"));

			thick=atof(FetchField(res_setAddFormsProj, "thickness"));

			if(fault>0.1 || fault<-0.1 ){

				fprintf(addformsproj[i].totFile, "%f %f\n", lastvs, lasttot+fault);

			}

			fprintf(addformsproj[i].totFile, "%f %f\n", vs, tot);

			lastvs=vs;

			lasttot=tot;

			lastfault=fault;

			if(vs>=minvs) {
				if(!first) firstvs=vs;
				if(first<2)	firsttot=tot;
				first++;
			}
		}

		// collect all of the "projection ahead" data (survey ID of -1)

		FreeResult(res_setAddFormsProj);
		sprintf(query, "select * from addformsdata where infoid=%ld and svyid=-1 order by md", addformsproj[i].id);
		if (DoQuery(res_setAddFormsProj, query)) {
			fprintf(stderr, "initAdditionalFormations: Error in select query\n");
			CloseDb();
			exit (-1);
		}

		fdi=fdi_start;

		while(FetchRow(res_setAddFormsProj)) {

			vs=atof(FetchField(res_setAddFormsProj, "vs"));
			tot=atof(FetchField(res_setAddFormsProj, "tot"));
			fault=atof(FetchField(res_setAddFormsProj, "fault"));
			thick=atof(FetchField(res_setAddFormsProj, "thickness"));

			// start of section that stores data for filled curves

			if(fdfirst)
			{
				sprintf(filldat[fdi],"%f %f",vs,tot);
			}
			else
			{
				sprintf(tmpstr," %f",tot);
				strcat(filldat[fdi],tmpstr);
			}
			fdi++; // record number of samples

			// start of section that stores data for filled curves

			if(fault>0.1 || fault<-0.1 ){
				fprintf(addformsproj[i].totFile, "%f %f\n", lastvs, lasttot+fault);
			}
			fprintf(addformsproj[i].totFile, "%f %f\n", vs, tot);

			lastvs=vs;
			lasttot=tot;
			lastfault=fault;
			if(vs>=minvs) {
				if(!first) firstvs=vs;
				if(first<2)	firsttot=tot;
				first++;
			}

		}
		fdfirst=0;

		FreeResult(res_setAddFormsProj);

	}

	for(i=0; i<MAX_ADDFORMS;i++) {
		if(addformsproj[i].totFile!=NULL) fclose(addformsproj[i].totFile);
	}

}

void buildAdditionalFormationFiles(void)
{
	int linestyle=30;
	int i=0;
	float vs, tot, fault;
	float lastvs=0.0, lasttot=0.0, lastfault=0.0;
	float firstvs, firsttot, rotangle, thick;
	int first;
	char query[256];

	if (DoQuery(res_setAddForms, "select * from addforms order by thickness")) {
		fprintf(stderr, "initAdditionalFormations: Error in select query\n");
		CloseDb();
		exit (-1);
	}

	// based on the formations found create the files that will be used to plot later

	i=0;
	numforms=0;
	while(FetchRow(res_setAddForms))
	{
		addforms[i].id=atol(FetchField(res_setAddForms, "id"));
		strcpy( addforms[i].color, FetchField(res_setAddForms, "color") );
		strcpy( addforms[i].label, FetchField(res_setAddForms, "label") );
		strcpy( addforms[i].tot_filename, tmpnam(NULL));
		addforms[i].totFile=fopen(addforms[i].tot_filename, "a+");
		strcpy(bg_color[i],FetchField(res_setAddForms,"bg_color"));
		strcpy(bg_percent[i],FetchField(res_setAddForms,"bg_percent"));
		strcpy(pat_color[i],FetchField(res_setAddForms,"pat_color"));
		strcpy(pat_num[i],FetchField(res_setAddForms,"pat_num"));
		strcpy(show_line[i],FetchField(res_setAddForms,"show_line"));
		// here is where we define the line width of the plots
		gnuplot_cmd(gplot, "set style line %d lt 1 lc rgb '#%s' lw 3 ", linestyle, addforms[i].color);
		numforms++;
		linestyle++;
		i++;
	}
	FreeResult(res_setAddForms);

	if(forcedminvs<99900.0 && forcedminvs>-99900.0)	minvs=forcedminvs;

	fdfirst=1;
	for(i=0; addforms[i].totFile != NULL; i++) {
		first=0;
		sprintf(query, "select * from addformsdata where infoid=%ld and projid=-1 order by md",addforms[i].id);
		if (DoQuery(res_setAddForms, query)) {
			fprintf(stderr, "initAdditionalFormations: Error in select query\n");
			CloseDb();
			exit (-1);
		}
		fdi=fdi_start;
		while(FetchRow(res_setAddForms))
		{
			vs=atof(FetchField(res_setAddForms, "vs"));
			tot=atof(FetchField(res_setAddForms, "tot"));
			fault=atof(FetchField(res_setAddForms, "fault"));
			thick=atof(FetchField(res_setAddForms, "thickness"));

			// start of section that stores data for filled curves

			if(fdfirst)
			{
				sprintf(filldat[fdi],"%f %f",vs,tot);
			}
			else
			{
				sprintf(tmpstr," %f",tot);
				strcat(filldat[fdi],tmpstr);
			}
			fdi++; // record number of samples

			// end of section that stores data for filled curves

			if(fault > 0.1 || fault < -0.1)
				fprintf(addforms[i].totFile, "%f %f\n", lastvs, lasttot+fault);
			fprintf(addforms[i].totFile, "%f %f\n", vs, tot);
			lastvs=vs;
			lasttot=tot;
			lastfault=fault;
			if(vs>=minvs) {
				if(!first) firstvs=vs;
				if(first<2)	firsttot=tot;
				first++;
			}
		}
		fdfirst=0;

		FreeResult(res_setAddForms);
		gnuplot_cmd(gplot,"set obj %d rect at %f,%f size char strlen('%s')+2, char 1",i+1,firstvs+50.0,firsttot,addforms[i].label);
		gnuplot_cmd(gplot,"set obj %d front clip lw 1.0 fc rgb 'white' fillstyle solid 1.00 border lt -1",i+1);
		gnuplot_cmd(gplot, "set label %d \"%s\" at %f,%f front center textcolor rgb '#000000'",i+1,addforms[i].label,firstvs+50.0,firsttot);
	}

	// close all the files that will plot the formations

	for(i=0; i<MAX_ADDFORMS;i++) {
		if(addforms[i].totFile != NULL) fclose(addforms[i].totFile);
	}
}

/*****************************************************************************/

void buildSurveyFiles(void) {
	int i;
	int iform=0;
	float ns, ew, md, inc, azm, vs, tvd;
	float tot, bot, fault, lastvs;
	float ca;
	int plan;

	if(plotType==PLOTTYPE_LAT) {
		sprintf(cmdstr,
		"SELECT * FROM surveys ORDER BY md");
		// "SELECT * FROM surveys WHERE inc>=%f AND md>=%f AND md<=%f ORDER BY md",
		// cutoffAngle, startdepth, enddepth);
	}
	else if(plotType==PLOTTYPE_POL) {
		sprintf(cmdstr,
		"SELECT * FROM surveys WHERE vs<=%f AND inc<=%f ORDER BY md",
		 enddepth, cutoffAngle);
	}
	else if(plotType==PLOTTYPE_VS) {
		sprintf(cmdstr,
		"SELECT * FROM surveys ORDER BY md");
	}
	else {
		sprintf(cmdstr,
		"SELECT * FROM surveys WHERE md>=%f AND md<=%f ORDER BY md",
		 startdepth,
		 enddepth);
	}

	if (DoQuery(res_set, cmdstr))
	{
		fprintf(stderr, "plotsvys: Error in select query for surveys\n");
		exit -1;
	}
	else
	{
		outfile=fopen(svyfn, "wt");
		totFile=fopen(totfn, "wt");
		botFile=fopen(botfn, "wt");
		projFile=fopen(projfn, "wt");
		if(!outfile || !totFile || !botFile || !projFile) {
			fprintf(stderr, "plotsvys: failed to open survey data file\n");
			FreeResult(res_set);
			exit -1;
		}
		svycnt=0;
		if(plotType==PLOTTYPE_POL)
			fprintf(outfile, "0.0 0.0\n");
		while(FetchRow(res_set)) {
			md =  atof( FetchField(res_set, "md") );
			// if(svycnt==0)	startdepth=md;
			ca =  atof( FetchField(res_set, "ca") );
			vs =  atof( FetchField(res_set, "vs") );
			tvd = atof( FetchField(res_set, "tvd") );
			ns = atof( FetchField(res_set, "ns") );
			ew = atof( FetchField(res_set, "ew") );
			inc = atof( FetchField(res_set, "inc") );
			azm = atof( FetchField(res_set, "azm") );
			tot = atof( FetchField(res_set, "tot") );
			bot = atof( FetchField(res_set, "bot") );
			plan = atof( FetchField(res_set, "plan") );
			fault = atof( FetchField(res_set, "fault") );
			//fprintf(stderr, "plotsvys: processing md:%f, tvd: %f and vs:%f\n",md,tvd,vs);
			if(inc<cutoffAngle && plotType==PLOTTYPE_LAT) {
				lastSvyTot=tot;
				continue;
			}

			if(inc>88.0 && cutoffDepth1<0.0) {
				cutoffDepth1=md;
			}
			if(inc>30.0 && cutoffDepth2<0.0) {
				cutoffDepth2=md;
			}

			if(plan==0) {
				if(md<minsvydepth)	minsvydepth=md;
				if(md>maxsvydepth)	maxsvydepth=md;
			}

			// rotate to proposed azimuth
			if(plotType==PLOTTYPE_HOR) rotate(&ns, &ew, propazm);

			if(md<minmd)	minmd=md;
			if(md>maxmd)	maxmd=md;
			if(tvd<mintvd)	mintvd=tvd;
			if(tvd>maxtvd)	maxtvd=tvd;
			if(vs<minvs)		minvs=vs;
			if(vs>maxvs)		maxvs=vs;
			if(ns<minns)		minns=ns;
			if(ns>maxns)		maxns=ns;
			if(ew<minew)		minew=ew;
			if(ew>maxew)		maxew=ew;
			if(bot>maxtvd)	maxtvd=bot;

			svycnt++;

			if(plan==0) {
				if(plotType==PLOTTYPE_VS) fprintf(outfile, "%f %f\n", vs, tvd);
				else if(plotType==PLOTTYPE_LAT) fprintf(outfile, "%f %f\n", vs, tvd);
				else if(plotType==PLOTTYPE_HOR) fprintf(outfile, "%f %f\n", ew, ns);
				else if(plotType==PLOTTYPE_POL) fprintf(outfile, "%f %f\n", ew, ns);
			}
			else {
				//fprintf(stderr, "plotsvys: bprj added to plot file with tvd: %f and vs:%f\n",tvd,vs);
				if(plotType==PLOTTYPE_VS) fprintf(projFile, "%f %f\n", vs, tvd);
				else if(plotType==PLOTTYPE_POL) fprintf(projFile, "%f %f\n", ew, ns);
				else if(plotType==PLOTTYPE_LAT) fprintf(projFile, "%f %f\n", vs, tvd);
				else if(plotType==PLOTTYPE_HOR) fprintf(projFile, "%f %f\n", ew, ns);
			}

			if(inc>=cutoffAngle && plotType==PLOTTYPE_LAT) {
				if(fault > 0.1 || fault < -0.1) {
					fprintf(totFile, "%f %f\n", lastvs, lastSvyTot+fault);
					fprintf(botFile, "%f %f\n", lastvs, lastSvyBot+fault);
				}
				fprintf(totFile, "%f %f\n", vs, tot);
				fprintf(botFile, "%f %f\n", vs, bot);
				lastSvyTot=tot;
				lastSvyBot=bot;
				lastvs=vs;
			}

			if(inc<=cutoffAngle && plotType==PLOTTYPE_VS) {
				if(fault > 0.1 || fault < -0.1) {
					fprintf(totFile, "%f %f\n", lastvs, lastSvyTot+fault);
					fprintf(botFile, "%f %f\n", lastvs, lastSvyBot+fault);
				}
				fprintf(totFile, "%f %f\n", vs, tot);
				fprintf(botFile, "%f %f\n", vs, bot);
				lastSvyTot=tot;
				lastSvyBot=bot;
				lastvs=vs;
			}
			bitvs=vs;
			bittvd=tvd;
			bittot=tot;
			bitbot=bot;
		}
		// enddepth=md;
		fclose(outfile);
		fclose(totFile);
		fclose(botFile);
		fclose(projFile);
	}

	FreeResult(res_set);



	if(plotType==PLOTTYPE_LAT) {

		sprintf(cmdstr,
		"SELECT md,inc,vs,tvd,ns,ew,azm,plan FROM wellplan\
		WHERE plan=0 AND inc>=%f AND md>=%f AND md<=%f ORDER BY md",
		cutoffAngle, startdepth, enddepth);
	}
	else if(plotType==PLOTTYPE_POL) {

		sprintf(cmdstr,
		"SELECT md,inc,vs,tvd,ns,ew,azm,plan FROM wellplan\
		WHERE plan=0 AND vs<=%f AND inc<=%f ORDER BY md",
		enddepth, cutoffAngle);
	}
	else if(plotType==PLOTTYPE_VS) {

		sprintf(cmdstr,
		"SELECT md,inc,vs,tvd,ns,ew,azm,plan FROM wellplan \
		 WHERE plan=0 ORDER BY md");
		 // WHERE plan=0 AND md>=%f AND md<=%f AND inc<=%f ORDER BY md",
		 // startdepth, enddepth, cutoffAngle);
	}
	else {
		sprintf(cmdstr, "SELECT * FROM wellplan WHERE plan=0 ORDER BY md");
		 // WHERE plan=0 AND md>=%f AND md<=%f ORDER BY md",
		 // startdepth, enddepth);
	}

	if (DoQuery(res_set, cmdstr))
	{
		fprintf(stderr, "plotsvys: Error in select query for wellplan\n");
		exit -1;
	}
	else
	{
		outfile=fopen(wpfn, "wt");
		if(!outfile) {
			fprintf(stderr, "plotsvys: failed to open wellplan data file\n");
			FreeResult(res_set);
			exit -1;
		}
		while(FetchRow(res_set)) {
			md =  atof( FetchField(res_set, "md") );
			vs =  atof( FetchField(res_set, "vs") );
			tvd = atof( FetchField(res_set, "tvd") );
			ns = atof( FetchField(res_set, "ns") );
			ew = atof( FetchField(res_set, "ew") );

			// rotate to proposed azimuth
			if(plotType==PLOTTYPE_HOR) rotate(&ns, &ew, propazm);

			if(md<minmd)	minmd=md;
			if(md>maxmd)	maxmd=md;
			if(tvd<mintvd)	mintvd=tvd;
			if(tvd>maxtvd)	maxtvd=tvd;
			if(vs<minvs)		minvs=vs;
			if(vs>maxvs)		maxvs=vs;
			if(ns<minns)		minns=ns;
			if(ns>maxns)		maxns=ns;
			if(ew<minew)		minew=ew;
			if(ew>maxew)		maxew=ew;

			if(plotType==PLOTTYPE_VS)
				fprintf(outfile, "%f %f\n", vs, tvd);
			else if(plotType==PLOTTYPE_LAT)
				fprintf(outfile, "%f %f\n", vs, tvd);
			else if(plotType==PLOTTYPE_HOR)
				fprintf(outfile, "%f %f\n", ew, ns);
			else if(plotType==PLOTTYPE_POL)
				fprintf(outfile, "%f %f\n", ew, ns);
		}
		fclose(outfile);
	}
	FreeResult(res_set);
}

/*****************************************************************************/
void buildCurrentPointFile(void){
	int count=1;
	int datasetid=0;
	float tvd,vs;
		sprintf(cmdstr,"Select dataset from appinfo limit 1");
		if (DoQuery(res_set, cmdstr)) {
				fprintf(stderr, "plotsvys: Error in select query for annotations\n");
				exit -1;
		} else {
			while(FetchRow(res_set)){
				datasetid =  atoi( FetchField(res_set, "dataset") );
			}
			FreeResult(res_set);
			if(datasetid==0){
				return;
			}
			sprintf(cmdstr,"select s.tvd,s.vs from surveys s left join welllogs w on s.md <= w.endmd and s.md >w.startmd where w.id=%i",datasetid);
			cpFile=fopen(cpfn, "a+");
			if(!cpFile) {
				fprintf(stderr, "plotsvys: failed to open anno data file\n");
				FreeResult(res_set);
				exit -1;
			}
			if(DoQuery(res_set,cmdstr)){
				fprintf(stderr, "plotsvys: Error in select query for dataset tvd and vs\n");
				exit -1;
			}else{
				while(FetchRow(res_set)) {
					tvd =  atof( FetchField(res_set, "tvd") );
					vs =  atof( FetchField(res_set, "vs") );
					fprintf(cpFile, "%f %f\n", vs, tvd);
					count++;

				}
			}
			FreeResult(res_set);
		}
		fclose(cpFile);
}

void buildAnnotationsFile(void){
	int count=1;
	float tvd,vs;
		sprintf(cmdstr,"Select tvd,vs from annos a left join surveys s on s.id=a.survey_id order by s.md asc");
		if (DoQuery(res_set, cmdstr)) {
				fprintf(stderr, "plotsvys: Error in select query for annotations\n");
				exit -1;
		} else {
			annoFile=fopen(annofn, "a+");
			if(!annoFile) {
				fprintf(stderr, "plotsvys: failed to open anno data file\n");
				FreeResult(res_set);
				exit -1;
			}
			while(FetchRow(res_set)) {
				tvd =  atof( FetchField(res_set, "tvd") );
				vs =  atof( FetchField(res_set, "vs") );
				fprintf(annoFile, "%f %f %i\n", vs, tvd,count);
				count++;

			}
			FreeResult(res_set);
		}
		fclose(annoFile);

}
void buildProjectionFile(void) {
	int i, plan;
	float ns, ew, md, inc, azm, vs, tvd;
	float ca;
	float tot, bot, fault, lastvs;
	int count=0;

	lastPrjTot=lastSvyTot;

	if(plotType==PLOTTYPE_LAT) {
		sprintf(cmdstr,
		"SELECT * FROM projections WHERE inc>=%f AND md>=%f AND md<=%f ORDER BY md",
		cutoffAngle,
		startdepth,
		enddepth);
	} else if(plotType==PLOTTYPE_POL) {
		sprintf(cmdstr,
		"SELECT * FROM projections WHERE vs<=%f AND inc<=%f ORDER BY md",
		 enddepth, cutoffAngle);
	} else if(plotType==PLOTTYPE_VS) {
		sprintf(cmdstr,
		"SELECT * FROM projections ORDER BY md");
		// "SELECT * FROM projections WHERE md>=%f AND md<=%f AND inc<=%f ORDER BY md",
		 // startdepth, enddepth, cutoffAngle);
	} else {
		sprintf(cmdstr, "SELECT * FROM projections ORDER BY md");
		// "SELECT * FROM projections WHERE md>=%f AND md<=%f ORDER BY md",
		 // startdepth, enddepth);
	} 

	if (DoQuery(res_set, cmdstr)) {
		fprintf(stderr, "plotsvys: Error in select query for projections\n");
		exit -1;
	} else {
		projFile=fopen(projfn, "a+");
		totFile=fopen(totfn2, "wt");
		botFile=fopen(botfn2, "wt");
		if(!projFile) {
			fprintf(stderr, "plotsvys: failed to open projection data file\n");
			FreeResult(res_set);
			exit -1;
		}
		while(FetchRow(res_set)) {
			md =  atof( FetchField(res_set, "md") );

			ca =  atof( FetchField(res_set, "ca") );
			vs =  atof( FetchField(res_set, "vs") );
			tvd = atof( FetchField(res_set, "tvd") );
			ns = atof( FetchField(res_set, "ns") );
			ew = atof( FetchField(res_set, "ew") );
			inc = atof( FetchField(res_set, "inc") );
			azm = atof( FetchField(res_set, "azm") );
			tot = atof( FetchField(res_set, "tot") );
			bot = atof( FetchField(res_set, "bot") );
			// plan = atof( FetchField(res_set, "plan") );
			fault = atof( FetchField(res_set, "fault") );
			// pin the tot/bot lines to the last survey tot/bot
			if(count==0) {
				startdepth=md;
				lastvs=bitvs;
				lastPrjTot=bittot;
				lastPrjBot=bitbot;
				/*	remove the bit-to-first-projection line segment
				if(inc>=cutoffAngle && plotType==PLOTTYPE_LAT) {
					fprintf(totFile, "%f %f\n", bitvs, bittot);
					fprintf(botFile, "%f %f\n", bitvs, bitbot);
					printf("vs:%f  tvd:%f\n", bitvs, bittot);
				}
				if(inc<=cutoffAngle && plotType==PLOTTYPE_VS) {
					fprintf(totFile, "%f %f\n", bitvs, bittot);
					fprintf(botFile, "%f %f\n", bitvs, bitbot);
				}
				*/
			}
			if(inc>88.0 && cutoffDepth1<0.0) { cutoffDepth1=md; }
			if(inc>30.0 && cutoffDepth2<0.0) { cutoffDepth2=md; }

			// rotate to proposed azimuth
			if(plotType==PLOTTYPE_HOR) rotate(&ns, &ew, propazm);

			if(md<minmd)	minmd=md;
			if(md>maxmd)	maxmd=md;
			if(tvd<mintvd)	mintvd=tvd;
			if(tvd>maxtvd)	maxtvd=tvd;
			if(vs<minvs)		minvs=vs;
			if(vs>maxvs)		maxvs=vs;
			if(ns<minns)		minns=ns;
			if(ns>maxns)		maxns=ns;
			if(ew<minew)		minew=ew;
			if(ew>maxew)		maxew=ew;
			if(bot>maxtvd)	maxtvd=bot;

			if(plotType==PLOTTYPE_VS) fprintf(projFile, "%f %f\n", vs, tvd);
			else if(plotType==PLOTTYPE_POL) fprintf(projFile, "%f %f\n", ew, ns);
			else if(plotType==PLOTTYPE_LAT) fprintf(projFile, "%f %f\n", vs, tvd);
			else if(plotType==PLOTTYPE_HOR) fprintf(projFile, "%f %f\n", ew, ns);
			//fprintf(stderr, "plotsvys: processing projection tvd:%f and vs:%f\n",tvd,vs);
			if(inc>=cutoffAngle && plotType==PLOTTYPE_LAT) {
				if(fault > 0.1 || fault < -0.1) {
					//fprintf(totFile, "%f %f\n", lastvs, lastPrjTot+fault);
					//fprintf(botFile, "%f %f\n", lastvs, lastPrjBot+fault);
					fprintf(totFile, "%f %f\n", vs, tot-fault);
					fprintf(botFile, "%f %f\n", vs, bot-fault);
					//fprintf(totFile, "%f %f\n", vs, tot);
					//fprintf(botFile, "%f %f\n", vs, bot);
				}
				fprintf(totFile, "%f %f\n", vs, tot);
				fprintf(botFile, "%f %f\n", vs, bot);

			}
			if(inc<=cutoffAngle && plotType==PLOTTYPE_VS) {
				if(fault > 0.1 || fault < -0.1) {
					// fprintf(totFile, "%f %f\n", lastvs, lastPrjTot+fault);
					// fprintf(botFile, "%f %f\n", lastvs, lastPrjBot+fault);
					fprintf(totFile, "%f %f\n", lastvs, tot);
					fprintf(botFile, "%f %f\n", lastvs, bot);
				}
				fprintf(totFile, "%f %f\n", vs, tot);
				fprintf(botFile, "%f %f\n", vs, bot);
				/*
				if(fault > 0.1 || fault < -0.1) {
					fprintf(totFile, "%f %f\n", vs, tot+fault);
					fprintf(botFile, "%f %f\n", vs, bot+fault);
				}
				*/
			}
			lastPrjTot=tot;
			lastPrjBot=bot;
			lastvs=vs;
			count++;
		}
		fclose(projFile);
		fclose(totFile);
		fclose(botFile);
	}
	fprintf(stderr, "plotsvys: processing projection\n");
	FreeResult(res_set);
}

/*****************************************************************************/

void buildTargetFiles(void) {
	int i, plan;
	float ns, ew, md, inc, azm, vs, tvd;

	if(plotType==PLOTTYPE_LAT) {
		sprintf(cmdstr,
		"SELECT md,inc,vs,tvd,ns,ew,azm,plan FROM wellplan\
		WHERE plan>0 ORDER BY vs");
	}
	else	return;

	if (DoQuery(res_set, cmdstr))
	{
		fprintf(stderr, "plotsvys: Error in select query for target changes\n");
		exit -1;
	}
	else
	{
		for(i=0;i<MAX_TARGETS;i++) {
			strcpy(targetfn[i], "\0");
			targetFile[i]=NULL;
		}
		while(FetchRow(res_set)) {
			vs =  atof( FetchField(res_set, "vs") );
			tvd = atof( FetchField(res_set, "tvd") );
			ns = atof( FetchField(res_set, "ns") );
			ew = atof( FetchField(res_set, "ew") );
			plan = atoi( FetchField(res_set, "plan") );
			if(plan<=MAX_TARGETS-1) {
				if(tvd<mintvd)	mintvd=tvd;
				if(tvd>maxtvd)	maxtvd=tvd;
				if(vs<minvs)		minvs=vs;
				if(vs>maxvs)		maxvs=vs;
				if(ns<minns)		minns=ns;
				if(ns>maxns)		maxns=ns;
				if(ew<minew)		minew=ew;
				if(ew>maxew)		maxew=ew;
				if(strlen(targetfn[plan])<=0 && targetFile[plan]==NULL) {
					strcpy(targetfn[plan], tmpnam(NULL));
					targetFile[plan]=fopen(targetfn[plan], "wt");
					if(!targetFile[plan]) {
						fprintf(stderr, "plotsvys: failed to open target file\n");
						FreeResult(res_set);
						exit -1;
					}
				}
				fprintf(targetFile[plan], "%f %f\n", vs, tvd);
			}
		}

		for(i=0;i<MAX_TARGETS;i++) {
			if(targetFile[i]!=NULL)
				fclose(targetFile[i]);
		}
	}
	FreeResult(res_set);
}


/*****************************************************************************/

void buildLogdataFile1(char* tablename) {
	int i;
	int curr;
	float depth, value;
	FILE *outfile;
	float x, y;
	if(svycnt<2)	return;

	if(plotType==PLOTTYPE_LAT)
		sprintf(cmdstr, "SELECT * FROM \"%s\" WHERE md>=%f AND md<=%f ORDER BY md ASC", tablename, minmd, cutoffDepth1);
	else if(plotType==PLOTTYPE_VS)
		sprintf(cmdstr, "SELECT * FROM \"%s\" ORDER BY md ASC", tablename);
	else
		sprintf(cmdstr, "SELECT * FROM \"%s\" WHERE md>=%f AND md<=%f ORDER BY md ASC", tablename, minmd, maxmd);
	if (DoQuery(res_set, cmdstr))
	{
		fprintf(stderr, "plotsvys: Error in select query for table %s\n", tablename);
		exit -1;
	}
	else
	{
		curr=-1;
		outfile=fopen(gdfn1, "a+");
		if(!outfile) {
			fprintf(stderr, "plotsvys: failed to open logging data file 1\n");
			FreeResult(res_set);
			exit -1;
		}
		while(FetchRow(res_set)) {
			depth = atof( FetchField(res_set, "tvd") );
			value = atof( FetchField(res_set, "value") );
			if(value>scaleright) value=scaleright;
			value=(value+plotbias)*plotscale;
			x=(value*yscale1);
			if(bUseLogScale>0 && x>0.0) {
				// x = log(x);
				x /= M_LN10;
			}
			if(x<mindataval)	mindataval=x;
			if(x>maxdataval)	maxdataval=x;
			y=depth;
			fprintf(outfile, "%f %f\n", minvs+x, y);
			// fprintf(outfile, "%f %f\n", x, y);
		}
		fclose(outfile);
	}
	FreeResult(res_set);
}


/*****************************************************************************/

void buildLogdataFile2(char* tablename) {
	int curr;
	float depth, value;
	FILE *outfile;
	float x, y;
	if(svycnt<2)	return;

	sprintf(cmdstr, "SELECT * FROM \"%s\" WHERE md>=%f AND md<=%f ORDER BY md ASC", tablename, cutoffDepth2, enddepth);
	if (DoQuery(res_set, cmdstr))
	{
		fprintf(stderr, "plotsvys: Error in select query for table %s\n", tablename);
		exit -1;
	}
	else
	{
		curr=-1;
		outfile=fopen(gdfn2, "a+");
		if(!outfile) {
			fprintf(stderr, "plotsvys: failed to open logging data file 1\n");
			FreeResult(res_set);
			exit -1;
		}

		while(FetchRow(res_set)) {
			depth = atof( FetchField(res_set, "vs") );
			value = atof( FetchField(res_set, "value") );
			x=depth;
			if(value>scaleright) value=scaleright;
			value=(value+plotbias)*plotscale;
			y=(value*yscale2);
			if(bUseLogScale>0 && y>0.0) {
				// y = log(y);
				y /= M_LN10;
			}
			if(y<mindataval)	mindataval=y;
			if(y>maxdataval)	maxdataval=y;
			fprintf(outfile, "%f %f\n", x, maxtvd-y);
		}
		fclose(outfile);
	}
	FreeResult(res_set);
}


/*****************************************************************************/

void buildControllogFile(char* tablename) {
	int i;
	FILE *outfile;
	float depth, value;

	sprintf(cmdstr, "SELECT * FROM \"%s\" WHERE md>=%f AND md<=%f ORDER BY md ASC",
		tablename, mintvd, maxtvd);
	if (DoQuery(res_set, cmdstr))
	{
		fprintf(stderr, "plotsvys: Error in select query for table %s\n", tablename);
		exit -1;
	}
	else
	{
		outfile=fopen(clfn, "a+");
		if(!outfile) {
			fprintf(stderr, "plotsvys: failed to open controllog data file 1\n");
			FreeResult(res_set);
			exit -1;
		}
		while(FetchRow(res_set)) {
			depth =  atof( FetchField(res_set, "md") );
			value = atof( FetchField(res_set, "value") );
			fprintf(outfile, "%f %f\n", minvs+value, depth);
		}
		fclose(outfile);
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void setStyles(void) {

	gnuplot_cmd(gplot, "set xtics offset 0,.5");
	if(plotType!=PLOTTYPE_POL)
		gnuplot_cmd(gplot, "set x2tics in offset 0,-.5");

	gnuplot_cmd(gplot, "set ytics in rotate offset 2.5,0");
	// gnuplot_cmd(gplot, "set ytics in rotate");
	if(plotType!=PLOTTYPE_POL)
		gnuplot_cmd(gplot, "set y2tics rotate offset -1,0");

	gnuplot_cmd(gplot, "set lmargin 1.75");
	gnuplot_cmd(gplot, "set rmargin 1.9");
	gnuplot_cmd(gplot, "set tmargin 1");
	gnuplot_cmd(gplot, "set bmargin 1");

	gnuplot_cmd(gplot, "set style line 20 lt 2 lc rgb 'black' lw 1 ");
	gnuplot_cmd(gplot, "set style line 21 lt 2 lc rgb 'gray' lw 1 ");
	gnuplot_cmd(gplot, "set grid xtics mxtics ls 20, ls 21");
	gnuplot_cmd(gplot, "set grid ytics mytics ls 20, ls 21");
	gnuplot_cmd(gplot, "set tics out scale .2");

	if(plotType==PLOTTYPE_VS || plotType==PLOTTYPE_LAT) {
		if(plotType==PLOTTYPE_VS) gnuplot_cmd(gplot, "set key inside left top samplen 0.7 box");
		else
		{
			gnuplot_cmd(gplot, "set key inside right top samplen 0.7 box");
		}
	}
	else gnuplot_cmd(gplot, "set key inside right top samplen 0.7 box");

	if(plotType==PLOTTYPE_VS || plotType==PLOTTYPE_LAT) {
		//gnuplot_cmd(gplot, "set xlabel 'Vertical Section' offset 0,0 ");
		//gnuplot_cmd(gplot, "set ylabel 'TVD' offset 0,0 ");
	}
	else {
		gnuplot_cmd(gplot, "set xlabel 'E/-W' offset 0,0 ");
		gnuplot_cmd(gplot, "set ylabel 'N/-S' offset 0,0 ");
	}

	gnuplot_cmd(gplot, "set style line 1 lt 2 lc rgb '#ff7070' lw 1 ");
	// surveys
	gnuplot_cmd(gplot, "set style line 2 lt 2 lc rgb 'black' lw 2 pt 2 ps .75 ");
	gnuplot_cmd(gplot, "set style line 3 lt 0 lc rgb 'black' lw 1 ");
	// tot and bot
//	gnuplot_cmd(gplot, "set style line 4 lt 1 lc rgb '#d00070' lw 3 ");
//	gnuplot_cmd(gplot, "set style line 5 lt 1 lc rgb '#d07000' lw 3 ");
	gnuplot_cmd(gplot, "set style line 4 lt 1 lc rgb '#%s' lw 3 ", plotcolortot);
	gnuplot_cmd(gplot, "set style line 5 lt 1 lc rgb '#%s' lw 3 ", plotcolorbot);
	// projected tot/bot
//	gnuplot_cmd(gplot, "set style line 11 lt 1 lc rgb '#%s' lw 3 ", plotcolortot);
//	gnuplot_cmd(gplot, "set style line 12 lt 1 lc rgb '#%s' lw 3 ", plotcolorbot);
	gnuplot_cmd(gplot, "set style line 11 lt 1 lc rgb '#d05050' lw 3 ");
	gnuplot_cmd(gplot, "set style line 12 lt 1 lc rgb '#d05050' lw 3 ");
	// wellplan
	//gnuplot_cmd(gplot, "set style line 6 lt 2 lc rgb '#00b000' lw 2 ");
	gnuplot_cmd(gplot, "set style line 6 lt 2 lc rgb '#%s' lw 2 ", plotcolorwp);
	gnuplot_cmd(gplot, "set style line 7 lt 2 lc rgb '#ff7000' lw 1 pt 6 ps .5 ");
	// survey points
	gnuplot_cmd(gplot, "set style line 8 lt 2 lc rgb '#0032FF' lw 1 pt 3 ps 1 ");
	gnuplot_cmd(gplot, "set style line 99 lt 2 lc rgb 'red' lw 1 pt 3 ps 1.5 ");
	// gamma
	gnuplot_cmd(gplot, "set style line 9 lt 2 lc rgb '#4040ff' lw 1");
	// projections
	gnuplot_cmd(gplot, "set style line 10 lt 26 lc rgb '#d00000' lw 2 pt 6 ps 1.7");
	gnuplot_cmd(gplot, "set style line 98 lt 2 lc rgb 'black' lw 3 pt 4 ps 1.7");

}

/*****************************************************************************/

void setScaling(void) {
	float scale;
	float x, y;
	const float nrmwidth=1200;
	const float nrmheight=600;

	if(plotType==PLOTTYPE_VS) {
		if(maxvs-minvs>1000.0) {
			minvs=((int)minvs-((int)minvs%100)-100) ;
			maxvs=((int)maxvs-((int)maxvs%100)+100);
		} else {
			minvs=((int)minvs-((int)minvs%100)-200) ;
			maxvs=((int)maxvs-((int)maxvs%10)+10);
		}
		if(maxtvd-mintvd>1000.0) {
			mintvd=((int)mintvd-((int)mintvd%10)-10);
			maxtvd=((int)maxtvd-((int)maxtvd%100)+100);
		} else {
			mintvd=((int)mintvd-((int)mintvd%1)-1);
			maxtvd=((int)maxtvd-((int)maxtvd%10)+50);
		}
	}
	else if(plotType==PLOTTYPE_LAT) {
		if(maxvs-minvs>1000.0) {
			minvs=((int)minvs-((int)minvs%100)-100) ;
			maxvs=((int)maxvs-((int)maxvs%100)+100);
		} else {
			minvs=((int)minvs-((int)minvs%10)-10) ;
			maxvs=((int)maxvs-((int)maxvs%10)+10);
		}
		if(maxtvd-mintvd>1000.0) {
			mintvd=((int)mintvd-((int)mintvd%10)-10);
			maxtvd=((int)maxtvd-((int)maxtvd%100)+100);
		} else {
			mintvd=((int)mintvd-((int)mintvd%1)-1);
			maxtvd=((int)maxtvd-((int)maxtvd%10)+50);
		}
	}
	else {
		mintvd=((int)mintvd-((int)mintvd%10));
		maxtvd=((int)maxtvd-((int)maxtvd%100)+200);
		minvs=((int)minvs-((int)minvs%100)-300) ;
		maxvs=((int)maxvs-((int)maxvs%100)+110);
		minns=((int)minns-((int)minns%100)-200) ;
		maxns=((int)maxns-((int)maxns%100)+200);
		minew=((int)minew-((int)minew%10)-200) ;
		maxew=((int)maxew-((int)maxew%10)+200);
	}
	/*
	if(plotType==PLOTTYPE_HOR) {
		if(maxns>maxew) maxew=maxns;
		else maxns=maxew;
		if(minns<minew) minew=minns;
		else minns=minew;

		if(maxns>-minns)	minns=-maxns;
		else maxns=-minns;
		if(maxew>-minew)	minew=-maxew;
		else maxew=-minew;
	}
	*/

	// try this instead
	if(forcedmintvd<99900.0&&forcedmintvd>-99900.0)	mintvd=forcedmintvd;
	if(forcedmaxtvd<99900.0&&forcedmaxtvd>-99900.0)	maxtvd=forcedmaxtvd;
	if(forcedminvs<99900.0&&forcedminvs>-99900.0)	minvs=forcedminvs;
	if(forcedmaxvs<99900.0&&forcedmaxvs>-99900.0)	maxvs=forcedmaxvs;

	if(plotType==PLOTTYPE_VS) {
		yscale1=((maxvs-minvs)*yfactor)/scaleright;
		yscale2=((maxtvd-mintvd)*yfactor)/scaleright;
	}
	else if(plotType==PLOTTYPE_LAT) {
		yscale1=((maxvs-minvs)*yfactor)/scaleright;
		yscale2=((maxtvd-mintvd)*yfactor)/scaleright;
	}
	else {
	}

	height=nrmheight;
	width=nrmwidth;

	if(plotType!=PLOTTYPE_POL && plotType!=PLOTTYPE_HOR) {
		height=420;
		width=1020;
		height=maxtvd-mintvd;
		width=maxvs-minvs;

		if(forcedheight>0)	height=forcedheight;
		if(forcedwidth>0)	width=forcedwidth;

		if(plotType==PLOTTYPE_VS) {
			// x = height/width*fabs(maxvs-minvs);
			// maxtvd=(maxtvd+(x/2.0));
			// mintvd=(mintvd-(x/2.0));
		}
		else if(forcedwidth<=0 && forcedheight>0) {
			/*
			x=fabs(maxtvd-mintvd);
			y=fabs(maxvs-minvs);
			if(y<x) {
				x=x-y;
				maxvs+=x;
			}
			y=height/fabs(maxtvd-mintvd);
			width=fabs(maxvs-minvs)*y*.5;
			if(width<nrmwidth) {
				width=nrmwidth;
				maxvs-=x;
			}
			*/
			height=nrmheight;
			width=nrmheight*2.0;
		}

		gnuplot_cmd(gplot, "set terminal pngcairo %s font \"arial,%d\" size %0.0f,%0.0f",
			transparent, fontsize, width, height);
		// gnuplot_cmd(gplot, "set terminal pngcairo small size %0.0f,%0.0f", width, height);

		gnuplot_cmd(gplot, "set yrange [%.0f:%.0f]", maxtvd, mintvd);
		gnuplot_cmd(gplot, "set y2range [%.0f:%.0f]", ((elev_ground + elev_rkb) - maxtvd), ((elev_ground + elev_rkb) - mintvd));
		gnuplot_cmd(gplot, "set xrange [%.0f:%.0f]", minvs, maxvs);
		gnuplot_cmd(gplot, "set x2range [%.0f:%.0f]", minvs, maxvs);

		if(width/fabs(maxvs-minvs)<.5) {
			gnuplot_cmd(gplot, "set xtics 500");
			gnuplot_cmd(gplot, "set x2tics 500");
			gnuplot_cmd(gplot, "set mxtics 5");
		}
		else if(width/fabs(maxvs-minvs)<2.5) {
			gnuplot_cmd(gplot, "set xtics 100");
			gnuplot_cmd(gplot, "set x2tics 100");
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else {
			gnuplot_cmd(gplot, "set xtics 10");
			gnuplot_cmd(gplot, "set x2tics 10");
			gnuplot_cmd(gplot, "set mxtics 5");
		}

		if(maxtvd-mintvd>2000) {
			gnuplot_cmd(gplot, "set ytics 500 rotate");
			gnuplot_cmd(gplot, "set y2tics 500");
			gnuplot_cmd(gplot, "set mytics 5");
		}
		else if(maxtvd-mintvd>1000) {
			gnuplot_cmd(gplot, "set ytics 100 rotate");
			gnuplot_cmd(gplot, "set y2tics 100");
			gnuplot_cmd(gplot, "set mytics 5");
		}
		else if(maxtvd-mintvd>150) {
			gnuplot_cmd(gplot, "set ytics 50 rotate");
			gnuplot_cmd(gplot, "set y2tics 50");
			gnuplot_cmd(gplot, "set mytics 5");
		}
		else {
			gnuplot_cmd(gplot, "set ytics 10 rotate");
			gnuplot_cmd(gplot, "set y2tics 10");
			gnuplot_cmd(gplot, "set mytics 5");
		}

	} else {
		if(plotType==PLOTTYPE_POL) {
			// image dimensions
			width=820;
			height=740;
			if(forcedwidth>0) {
				width=forcedwidth;
				height=width*.902439024;
			}
			if(forcedheight>0)	height=forcedheight;
			gnuplot_cmd(gplot, "set angles degrees");
			gnuplot_cmd(gplot, "set grid polar 30");
			gnuplot_cmd(gplot, "set terminal pngcairo font \"arial,%d\" size %f,%f",
				fontsize, width, height);

			// scaled dimensions
			height=(fabs(maxns)>fabs(minns)?fabs(maxns):fabs(minns));
			width=(fabs(maxew)>fabs(minew)?fabs(maxew):fabs(minew));
			if(width>height) height=width; else width=height;

			gnuplot_cmd(gplot, "set yrange [%.0f:%.0f]", -height, height);
			gnuplot_cmd(gplot, "set y2range [%.0f:%.0f]", -height, height);
			gnuplot_cmd(gplot, "set xrange [%.0f:%.0f]", -width, width);
			gnuplot_cmd(gplot, "set x2range [%.0f:%.0f]", -width, width);

			if(maxew-minew>10000.0 || maxns-minns>10000.0) {
				gnuplot_cmd(gplot, "set xtics 1000");
				gnuplot_cmd(gplot, "set ytics 1000");
				gnuplot_cmd(gplot, "set mxtics 5");
				gnuplot_cmd(gplot, "set mytics 5");
				// gnuplot_cmd(gplot, "set x2tics 500");
				// gnuplot_cmd(gplot, "set y2tics 500");
			} else if(maxew-minew>1000.0 || maxns-minns>1000.0) {
				gnuplot_cmd(gplot, "set xtics 500");
				gnuplot_cmd(gplot, "set ytics 500");
				gnuplot_cmd(gplot, "set mxtics 5");
				gnuplot_cmd(gplot, "set mytics 5");
				// gnuplot_cmd(gplot, "set x2tics 500");
				// gnuplot_cmd(gplot, "set y2tics 500");
			} else if(maxew-minew>100.0 || maxns-minns>100.0) {
				gnuplot_cmd(gplot, "set xtics 50");
				gnuplot_cmd(gplot, "set ytics 50");
				gnuplot_cmd(gplot, "set mxtics 5");
				gnuplot_cmd(gplot, "set mytics 5");
				// gnuplot_cmd(gplot, "set x2tics 100");
				// gnuplot_cmd(gplot, "set y2tics 100");
			} else {
				gnuplot_cmd(gplot, "set xtics 10");
				gnuplot_cmd(gplot, "set ytics 10");
				gnuplot_cmd(gplot, "set mxtics 1");
				gnuplot_cmd(gplot, "set mytics 1");
				// gnuplot_cmd(gplot, "set x2tics 10");
				// gnuplot_cmd(gplot, "set y2tics 10");
			}
		} else {	// PLOTTYPE_HOR
			height=nrmheight;
			// width=nrmheight*1.5;
			width=nrmheight*2.0;
			// height=(fabs(maxns)>fabs(minns)?fabs(maxns):fabs(minns));
			// width=(fabs(maxew)>fabs(minew)?fabs(maxew):fabs(minew));
			// if(width>height) height=width; else width=height;

			gnuplot_cmd(gplot, "set label 'Proposed Line Of Azimith: %.2f --->>' at %f,0 offset 0,.6 front font \"arial,%d\"",
				degrees(propazm), fabs(maxew-minew)/2.0, fontsize+3);

			gnuplot_cmd(gplot, "set terminal pngcairo font \"arial,%d\" size %0.0f,%0.0f", fontsize, width, height);
			gnuplot_cmd(gplot, "set yrange [%.0f:%.0f]", minns, maxns);
			gnuplot_cmd(gplot, "set y2range [%.0f:%.0f]", minns, maxns);
			gnuplot_cmd(gplot, "set xrange [%.0f:%.0f]", minew, maxew);
			gnuplot_cmd(gplot, "set x2range [%.0f:%.0f]", minew, maxew);

			if(maxew-minew>3000) {
				gnuplot_cmd(gplot, "set xtics 500");
				gnuplot_cmd(gplot, "set mxtics 2");
				gnuplot_cmd(gplot, "set x2tics 500");
			}
			else if(maxew-minew>1000) {
				gnuplot_cmd(gplot, "set xtics 100");
				gnuplot_cmd(gplot, "set mxtics 10");
				gnuplot_cmd(gplot, "set x2tics 100");
			}
			else if(maxew-minew>100) {
				gnuplot_cmd(gplot, "set xtics 50");
				gnuplot_cmd(gplot, "set mxtics 5");
				gnuplot_cmd(gplot, "set x2tics 50");
			}
			else {
				gnuplot_cmd(gplot, "set xtics 10");
				gnuplot_cmd(gplot, "set mxtics 10");
				gnuplot_cmd(gplot, "set x2tics 10");
			}

			if(maxns-minns>5000) {
				gnuplot_cmd(gplot, "set ytics 1000");
				gnuplot_cmd(gplot, "set mytics 5");
				gnuplot_cmd(gplot, "set y2tics 1000");
			}
			else if(maxns-minns>2000) {
				gnuplot_cmd(gplot, "set ytics 500");
				gnuplot_cmd(gplot, "set mytics 2");
				gnuplot_cmd(gplot, "set y2tics 500");
			}
			else if(maxns-minns>1000) {
				gnuplot_cmd(gplot, "set ytics 100");
				gnuplot_cmd(gplot, "set mytics 5");
				gnuplot_cmd(gplot, "set y2tics 100");
			}
			else if(maxns-minns>100) {
				gnuplot_cmd(gplot, "set ytics 50");
				gnuplot_cmd(gplot, "set mytics 5");
				gnuplot_cmd(gplot, "set y2tics 50");
			}
			else {
				gnuplot_cmd(gplot, "set ytics 10");
				gnuplot_cmd(gplot, "set mytics 10");
				gnuplot_cmd(gplot, "set y2tics 10");
			}
		}
	}

	unlink(outFilename);
	gnuplot_cmd(gplot, "set output \"%s\"", outFilename);
}

/*****************************************************************************/

void barfAndDie(char* progname) {
	printf("Usage: %s -t <[vs | lat | hor | pol]>\tplot type default: vs\n\
	{-d dbname}\n\
	{-p ProjectedSurveyLength}\tdefault: 100\n\
	{-T TablenameOfDataToPlot}\tdefault: excluded\n\
	{-a AmountOfDataToAverage}\tdefault: 8\n\
	{-s StartDepth}\tdefault: 0\n\
	{-e EndDepth}\tdefault: 99999.0\n\
	{-c CutoffAngle}\tdefault: 20\n\
	{-yscale yScaleFactor}\tdefault: .10\n\
	{-h forced height}\n\
	{-w forced width}\n\
	{-tvd1 forcedMinTVD}\n\
	{-tvd2 forcedMaxTVD}\n\
	{-vs1 forcedMinVS}\n\
	{-vs2 forcedMaxVS}\n\
	{-f font size}\n\
	{-transparent}\tdefault: white\n\
	{-o OutputFilename}\tdefault: surveyplot.png\n\
plottype = [vs | lat | hor | pol]\n\
", progname);
	exit(1);
}

/*****************************************************************************/

int main(int argc, char * argv[])
{
    int  i, target, inum;
	char buf[1024];
	char whatToPlot[256];
	char controllogName[256];
	char opts[256];
	float sd, ed;
	float lastsd, lasted;
	float svs, evs;
	float fnum;
	struct stat fstat;

	strcpy(filldfn,"");
	fdi = 0;
	for(i=0; i<MAX_ADDFORMS; i++)
	{
		strcpy(bg_color[i],"");
		strcpy(bg_percent[i],"");
		strcpy(pat_color[i],"");
		strcpy(pat_num[i],"");
		strcpy(show_line[i],"");
	}

	sd=lastsd=ed=lasted=svs=evs=0;

	// CheckForValidKey();
	keycheckOK=1;
	if(!keycheckOK) {
		fprintf(stderr, "Error: No security key found!\n");
		return(-1);
	}


	avgsz=8;
	plotType=PLOTTYPE_VS;
	projlen=100;
	startdepth=0.0;
	enddepth=99999.0;
	cutoffAngle=60;
	strcpy(outFilename, "surveyplot.png");
	strcpy(outFilename2, "surveyplot-1.png");
	strcpy(outFilename3, "surveyplot-2.png");
	strcpy(outFilename4, "surveyplot-3.png");
	strcpy(opts, "\0");
	strcpy(whatToPlot, "\0");
	strcpy(controllogName, "\0");
	strcpy(dbname, "\0");
	for(i=0;i<MAX_ADDFORMS;i++) {
		strcpy(addforms[i].tot_filename, "\0");
		strcpy(addformsproj[i].tot_filename, "\0");
		strcpy(addforms[i].bot_filename, "\0");
		strcpy(addformsproj[i].bot_filename, "\0");
		addforms[i].totFile=NULL;
		addformsproj[i].totFile=NULL;
		addforms[i].botFile=NULL;
		addformsproj[i].botFile=NULL;
	}
	for(i=0;i<MAX_TARGETS;i++)
		strcpy(targetfn[i], "\0");
	forcedwidth=forcedheight=-1;
	forcedmintvd=forcedminvs=-999990.0;
	forcedmaxtvd=forcedmaxvs=999999.0;
	strcpy(transparent, " \0");

	if(argc<4)	barfAndDie(argv[0]);
	for(i=1; i < argc; i++)
	{
		if(!strcmp(argv[i], "-t")) {
			strcpy( buf, argv[++i] );
			if(strcmp(buf, "vs")==0)	plotType=PLOTTYPE_VS;
			else if(strcmp(buf, "hor")==0)	plotType=PLOTTYPE_HOR;
			else if(strcmp(buf, "lat")==0)	plotType=PLOTTYPE_LAT;
			else if(strcmp(buf, "pol")==0)	plotType=PLOTTYPE_POL;
			else barfAndDie(argv[0]);
		}
		else if(!strcmp(argv[i], "-T"))
			strcpy(whatToPlot, argv[++i]);
		else if(!strcmp(argv[i], "-a"))
			avgsz=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-p"))
			projlen=atof(argv[++i]);
		else if(!strcmp(argv[i], "-s"))
			startdepth=atof(argv[++i]);
		else if(!strcmp(argv[i], "-e"))
			enddepth=atof(argv[++i]);
		else if(!strcmp(argv[i], "-c"))
			cutoffAngle=atof(argv[++i]);
		else if(!strcmp(argv[i], "-h"))
			forcedheight=atof(argv[++i]);
		else if(!strcmp(argv[i], "-w"))
			forcedwidth=atof(argv[++i]);
		else if(!strcmp(argv[i], "-tvd1"))
			forcedmintvd=atof(argv[++i]);
		else if(!strcmp(argv[i], "-tvd2"))
			forcedmaxtvd=atof(argv[++i]);
		else if(!strcmp(argv[i], "-vs1"))
			forcedminvs=atof(argv[++i]);
		else if(!strcmp(argv[i], "-vs2"))
			forcedmaxvs=atof(argv[++i]);
		else if(!strcmp(argv[i], "-yscale"))
			yfactor=atof(argv[++i]);
		else if(!strcmp(argv[i], "-f"))
			fontsize=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-transparent"))
			strcpy(transparent, "transparent");
		else if(!strcmp(argv[i], "-nodata"))
			bNoData=1;
		else if(!strcmp(argv[i], "-o")) {
			strcpy(outFilename, argv[++i]);
			strcpy(outFilename2, outFilename);
			strcat(outFilename2, "1.png");
			strcpy(outFilename3, outFilename);
			strcat(outFilename3, "2.png");
			strcpy(outFilename4, outFilename);
			strcat(outFilename4, "3.png");
		}
		else if (!strcmp (argv[i], "-d"))
			strcpy(dbname, argv[++i]);
		else if( !strcmp(argv[i], "-anno"))
			anno=1;
		else barfAndDie(argv[0]);
	}
	if(strlen(dbname)<=0) {
		barfAndDie(argv[0]);
	}
	if (OpenDb(argv[0], dbname, "umsdata", "umsdata") != 0)
	{
		fprintf(stderr, "Failed to open database\n");
		exit(-1);
	}

	readAppinfo(argv[0]);
	readWellinfo(argv[0]);

	minsvydepth=minmd=minns=minew=mintvd=minvs=99999.0;
	maxsvydepth=maxmd=maxns=maxew=maxtvd=maxvs=-99999.0;

	strcpy(clfn, tmpnam(NULL));
	strcpy(svyfn, tmpnam(NULL));
	strcpy(wpfn, tmpnam(NULL));
	strcpy(gdfn1, tmpnam(NULL));
	strcpy(gdfn2, tmpnam(NULL));
	strcpy(projfn, tmpnam(NULL));
	strcpy(annofn, tmpnam(NULL));
	strcpy(cpfn, tmpnam(NULL));
	strcpy(totfn, tmpnam(NULL));
	strcpy(botfn, tmpnam(NULL));
	strcpy(totfn2, tmpnam(NULL));
	strcpy(botfn2, tmpnam(NULL));

	gplot = gnuplot_init();
	if(gplot==NULL) {
		fprintf(stderr, "Failed to create gnuplot_i object\n");
		exit(-1);
	}

	buildSurveyFiles();

	buildProjectionFile();

	buildCurrentPointFile();

	// buildTargetFiles();
	if( plotType==PLOTTYPE_LAT )

		if(anno>0){
			buildAnnotationsFile();

		}

		buildAdditionalFormationFiles();

		buildAdditionalFormationFilesProj();

		// store the data of all formations in a single file used for fills

		if(numforms > 0 && fdi > 0)
		{
			strcpy(filldfn,tmpnam(NULL));
			fdFile = fopen(filldfn, "a+");
//			printf("sses_ps: fdi=%d filldfn=%s\n",fdi,filldfn);
			for(i=0; i<fdi; i++)
			{
//				printf("sses_ps: i=%d %s\n",i,filldat[i]);
				fprintf(fdFile,"%s\n",filldat[i]);
			}
			fclose(fdFile);
		}

        DoQuery(res_set, "SELECT * FROM wellinfo");
        if(FetchRow(res_set)) {
		strcpy(plotcolortot, FetchField(res_set, "colortot"));
		strcpy(plotcolorbot, FetchField(res_set, "colorbot"));
		strcpy(plotcolorwp, FetchField(res_set, "colorwp"));
		vslon=atoi(FetchField(res_set,"vslon"));
		vsland=atof(FetchField(res_set,"vsland"));
		vsldip=atof(FetchField(res_set,"vsldip"));

    }

	FreeResult(res_set); 
	setStyles();


	// slice up the available area to scale the gamma

	// maxtvdplot=((int)maxtvd-((int)maxtvd%100))+100;
	// minvs=(int)(minvs-((int)minvs%100));
	setScaling();

	// plot the well logs
	if(strlen(whatToPlot)) {
		buildLogdataFile1(whatToPlot);

		buildLogdataFile2(whatToPlot);

	} else {

		if(!bNoData) {
			sprintf(cmdstr, "SELECT * FROM welllogs ORDER BY startmd;");
			if (DoQuery(res_set2, cmdstr)) {
				fprintf(stderr, "%s: Error in select query for table %s\n", argv[0], cmdstr);
				FreeResult(res_set);
				CloseDb();
				exit (-1);
			}
			while(FetchRow(res_set2)) {
				strcpy(whatToPlot, FetchField(res_set2, "tablename"));
				sd=atof(FetchField(res_set2, "startdepth"));
				ed=atof(FetchField(res_set2, "enddepth"));
				svs=atof(FetchField(res_set2, "startvs"));
				evs=atof(FetchField(res_set2, "endvs"));

				buildLogdataFile1(whatToPlot);
				buildLogdataFile2(whatToPlot);

				lasted=ed;
				lastsd=sd;
			}
		}
		sprintf(cmdstr, "SELECT * FROM controllogs ORDER BY startmd;");
		if (DoQuery(res_set2, cmdstr)==0) {
			if(FetchRow(res_set2)) {
				strcpy(controllogName, FetchField(res_set2, "tablename"));
				controlTot=atof(FetchField(res_set2, "tot"));
				controlBot=atof(FetchField(res_set2, "bot"));
			}
			FreeResult(res_set2);
			buildControllogFile(controllogName);
		}
	}

	for(i=0;i<MAXPLOTS;i++)	plotstr[i][0]='\0';

	i=0;
	sprintf(plotstr[i++], "plot '%s' with lines ls 6 t 'Wellplan'", wpfn);
	if( plotType==PLOTTYPE_POL )
		sprintf(plotstr[i++], ", '%s' with points ls 7 t ''", wpfn);
	
//	for(target=0;target<MAX_TARGETS;target++) {
//		if(strlen(targetfn[target])) {
//			sprintf(plotstr[i++], ", '%s' with linespoints ls %d t 'Target %d'",
//			targetfn[target], 10+target, target);
//		}
//	}

	if(strlen(whatToPlot) && (plotType==PLOTTYPE_VS || plotType==PLOTTYPE_LAT) ) {
		sprintf(plotstr[i++], ", '%s' with lines ls 9 t 'Gamma'", gdfn1);
		sprintf(plotstr[i++], ", '%s' with lines ls 9 t ''", gdfn2);
	}

	for(target=0; target < MAX_ADDFORMS; target++) {
		if(strlen(addforms[target].tot_filename)) {
			stat(addforms[target].tot_filename, &fstat);
			if(fstat.st_size > 0)
			{
//				printf("sses_ps: filename = %s\n",addforms[target].tot_filename);
				if(!strcmp(show_line[target],"Yes"))
					sprintf(plotstr[i++], ", '%s' with lines ls %d t '%s'",
						addforms[target].tot_filename, 30+target, addforms[target].label);
			}
//			stat(addforms[target].bot_filename, &fstat);
//			if(fstat.st_size>0) {
//				sprintf(plotstr[i++], ", '%s' with lines ls %d t '%s'",
//					addforms[target].bot_filename, 30+target, addforms[target].label);
//			}
		}
		else break;
	}

	//sprintf(plotstr[i++], ", '%s' with lines ls 5 t 'BOTW'", botfn);
	sprintf(plotstr[i++], ", '%s' with lines ls 4 t 'TCL'", totfn);

	stat(botfn2, &fstat);
	if(fstat.st_size>0)
		sprintf(plotstr[i++], ", '%s' with lines ls 12 t ''", botfn2);
	stat(totfn2, &fstat);
	if(fstat.st_size>0)
		sprintf(plotstr[i++], ", '%s' with lines ls 11 t ''", totfn2);

	// if(strlen(controllogName) && (plotType==PLOTTYPE_VS || plotType==PLOTTYPE_LAT) ) {
		// sprintf(plotstr[i++], ", '%s' with lines ls 1 t 'Control'", clfn);
	// }

//	sprintf(plotstr[i++], ", '%s' with lines ls 2 t 'Surveys'", svyfn);
	sprintf(plotstr[i++], ", '%s' with lines lw 8 lc -1 t 'Surveys'", svyfn);
	sprintf(plotstr[i++], ", '%s' with lines lw 4 lc rgb '#ffffff' notitle", svyfn);
//	sprintf(plotstr[i++], ", '%s' with points ls 8 t ''", svyfn);
	sprintf(plotstr[i++], ", '%s' with points ls 8 t ''", svyfn);
	stat(projfn, &fstat);
	if(fstat.st_size>0) sprintf(plotstr[i++], ", '%s' with points ls 10 t 'Projected'", projfn);
	stat(annofn,&fstat);
	if(fstat.st_size>0) sprintf(plotstr[i++],", '%s' with labels font \"arial,18\" t ''",annofn);
	stat(cpfn,&fstat);
	if(fstat.st_size>0) sprintf(plotstr[i++],", '%s' with points ls 99 t ''",cpfn);
	if(vslon==1 && plotType==PLOTTYPE_LAT)
	{
		if(DoQuery(res_set,"select * from surveys where plan=1")==0)
		{
			FetchRow(res_set);
			float btprjvs = atof(FetchField(res_set,"vs"));
			float bttvd   = atof(FetchField(res_set,"tvd"));
			float bttcl     = atof(FetchField(res_set,"tot"));
			float vsltvd = (btprjvs - vsland)*tan((vsldip*(M_PI/180)))+bttvd+(bttcl-bttvd);
//			printf("sses_ps: x:%f y:%f \n",vsland,vsltvd);
			sprintf(plotstr[i++],", \"<echo '%f %f'\" with points ls 98 t 'Fixed Landing'",vsland,vsltvd);
			gnuplot_cmd(gplot,"set label \"%.2fvs-%.2fTVD\" at %f,%f front textcolor rgb 'black' offset 0,-3 font \"arial,18\"",
				vsland,vsltvd,vsland,vsltvd);
			FreeResult(res_set);
		}
		else
		{
			printf("query failure\n");
		}
	}
	if(plotType==PLOTTYPE_LAT)
	{
//		printf("sses_ps: outFilename = %s\n",outFilename);
		if((strstr(outFilename,"_gva_tab5.png")!=NULL && label_dwebplot) || (strstr(outFilename,"surveyplotlat.png")!=NULL && label_dreport))
		{
			char cquery[1024];
			sprintf(cquery,"select * from surveys where plan=0 and md >= %f order by md desc",labeling_start);
			if(DoQuery(res_set,cquery)==0)
			{
				int count_since_last=label_every;
				while(FetchRow(res_set))
				{
					if(count_since_last >= label_every)
					{
						count_since_last=1;
						float c_md = atof(FetchField(res_set,"md"));
						float ctvd = atof(FetchField(res_set,"tvd"));
						float cvs  = atof(FetchField(res_set,"vs"));
						char clabel[1024];
						if(label_dmd==1 && label_dvs==1)
						{
							sprintf(clabel,"%.2fMD %.2fVS",c_md,cvs);
						}
						else if(label_dmd==1 && label_dvs==0)
						{
							sprintf(clabel,"%.2fMD",c_md);
						}
						else if(label_dmd==0 && label_dvs==1)
						{
							sprintf(clabel,"%.2fVS",cvs);
						}
						else
						{
							count_since_last++;
							continue;
						}
						if(label_orient==1)
						{
							gnuplot_cmd(gplot,"set label \"%s\" at %f,%f front textcolor rgb 'black' offset 0,0 font \"arial,12\"",clabel,cvs,ctvd);
						}
						else
						{
							gnuplot_cmd(gplot,"set label \"%s\" at %f,%f front textcolor rgb 'black' offset 0,2 font \"arial,12\" rotate 90",clabel,cvs,ctvd);
						}
					}
					else
					{
						count_since_last++;
					}
				}
				FreeResult(res_set);
			}
			else
			{
				printf("label query failure\n");
			}
		}
	}
//	printf("sses_ps: MAX_ADDFORMS = %d\n",MAX_ADDFORMS);

	// create the lines for the different formations

	for(target=0;target<MAX_ADDFORMS;target++)
	{
		if(strlen(addformsproj[target].tot_filename))
		{
//			printf("sses_ps: tot_filename = %s\n",addformsproj[target].tot_filename);
			stat(addformsproj[target].tot_filename, &fstat);
//			if(!strcmp(show_line[target],"Yes"))
				sprintf(plotstr[i++], ", '%s' w lines ls %d t '%s'", addformsproj[target].tot_filename,90+target, "");
		}
		else break;
	}

	// sample output:
	// plot '/tmp/fileouDHv0' with lines ls 6 t 'Wellplan', '/tmp/fileVMZ5qX' with lines ls 30 t 'Witchita Albany', '/tmp/fileLKu0ny' with lines ls 31 t 'Dean', '/tmp/fileEZpVk9' with lines ls 32 t 'Wolfcamp A', '/tmp/filezRFQhK' with lines ls 33 t 'TOT', '/tmp/file4ImMel' with lines ls 34 t 'BOT', '/tmp/file0YYlPz' with lines ls 4 t 'TCL', '/tmp/fileorocum' with lines ls 12 t '', '/tmp/file7CwfBL' with lines ls 11 t '', '/tmp/fileEfCLCp' with lines ls 2 t 'Surveys', '/tmp/fileEfCLCp' with points ls 8 t '', '/tmp/file8DlwaN' with points ls 10 t 'Projected', '/tmp/filefgNs3n' with labels font "arial,18" t '', '/tmp/file5KkpWY' with points ls 99 t '', '/tmp/filew3SJdx' with lines ls 90 t '', '/tmp/filevQ7Ld8' with lines ls 91 t '', '/tmp/filewjKOdJ' with lines ls 92 t '', '/tmp/fileAwHRdk' with lines ls 93 t '', '/tmp/filepA1UdV' with lines ls 94 t ''

//	strcpy(cmdstr,"plot '");
//	strcat(cmdstr,filldfn);
//	strcat(cmdstr,"' using 1:2:3 with filledcurves lc rgb 'red' t 'Wellplan'");
//	printf("sses_ps: cmdstr=%s\n",cmdstr);

	for(i=0,cmdstr[0]='\0'; i < MAXPLOTS; i++)
	{
		strcat(cmdstr,plotstr[i]);
		if(strlen(plotstr[i]) < 1) continue;
		printf("sses_ps: plotstr %d %s\n",i,plotstr[i]);
	}

	// if we a plotting and the formation are more than one then create a fill between formations

	if(strncmp(cmdstr,"plot ",4) == 0 && numforms > 1 && strlen(filldfn) > 0)
	{
		strcpy(tstr,"set style fill transparent solid 0.4 noborder");
		gnuplot_cmd(gplot,tstr);

		strcpy(tmpstr,&cmdstr[5]);
		strcpy(cmdstr,"");
		for(i=0; i<(numforms-1); i++)
		{
			if(i == 0) strcpy(cmdstr,"plot ");
			if(strlen(bg_color[i]) > 0 && strlen(bg_percent[i]) > 0)
			{
				sscanf(bg_percent[i],"%f",&fnum);
				if(fnum >= 0.1)
				{
					sprintf(tstr,"'%s' u 1:%d:%d w filledcurves fs transparent solid %s lc rgb '#%s' notitle, ",
						filldfn,i+2,i+3,bg_percent[i],bg_color[i]);
					strcat(cmdstr,tstr);
					strcpy(filldfn,"");
				}
			}
			if(strlen(pat_color[i]) > 0 && strlen(pat_num[i]) > 0)
			{
				sscanf(pat_num[i],"%d",&inum);
				if(inum > 0)
				{
					sprintf(tstr,"'%s' u 1:%d:%d w filledcurves fs transparent pattern %s lc rgb '#%s' notitle, ",
						filldfn,i+2,i+3,pat_num[i],pat_color[i]);
					strcat(cmdstr,tstr);
					strcpy(filldfn,"");
				}
			}
		}
		strcat(cmdstr,tmpstr);
//		i = strlen(cmdstr);
//		cmdstr[i - 2] = '\0';
		printf("sses_ps: len=%ld cmdstr=%s\n",strlen(cmdstr),cmdstr);
	}

	gnuplot_cmd(gplot, cmdstr);

//	if(fdi > 0 && strlen(filldfn) > 0)
//	{
//		strcpy(cmdstr,"set style fill transparent solid 0.4 noborder");
//		printf("sses_ps: cmdstr=%s\n",cmdstr);
//		gnuplot_cmd(gplot,cmdstr);
//		for(i=0,cmdstr[0]='\0'; i<1; i++)
//		{
//			sprintf(cmdstr,"plot '%s' using 1:2:3 notitle with filledcurves lc rgb 'red'",filldfn);
//		}
//		printf("sses_ps: cmdstr=%s\n",cmdstr);
//	    gnuplot_cmd(gplot, cmdstr);
//	}

	gnuplot_close(gplot);

	for(i=0;i<MAX_ADDFORMS;i++)
	{
		if(strlen(addforms[i].tot_filename)) unlink(addforms[i].tot_filename);
		if(strlen(addformsproj[i].tot_filename)) unlink(addformsproj[i].tot_filename);
		if(strlen(addforms[i].bot_filename)) unlink(addforms[i].bot_filename);
		if(strlen(addformsproj[i].bot_filename)) unlink(addformsproj[i].bot_filename);
	}
	unlink(clfn);
	unlink(svyfn);
	unlink(wpfn);
	unlink(gdfn1);
	unlink(gdfn2);
	unlink(projfn);
	unlink(annofn);
	unlink(cpfn);
	unlink(totfn);
	unlink(botfn);
	unlink(totfn2);
	unlink(botfn2);

	// remove the temporary file

	if(numforms > 1 && strlen(filldfn) > 0) unlink(filldfn);

	// sprintf(cmdstr, "./sses_pd -d %s -r 600 -nd -T %s -o %s -s %f -e %f -w 100 -h %f",
	// dbname, controllogName, outFilename2, mintvd, maxtvd, height);

	if(bUseLogScale)	strcpy(opts, "-log");

	// if(strlen(whatToPlot) && (plotType==PLOTTYPE_LAT) ) {
	// "./sses_dsp -d %s -r %f -o %s -w %f -h %f -s %f -e %f -rotate -nd -grid -nomargin -vs -pstart %f -pend %f -color %s",
	
	if(plotType==PLOTTYPE_LAT)
	{
		sprintf(cmdstr,
			"./sses_gamma -d %s -r %f -o %s -w %f -h %f -s %f -e %f -pstart %f -pend %f -color %s -rotate -grid -vs -nd %s",
			dbname,
			// scaleright, 
			yfactor,
			outFilename4,
			100.0,
			width,
			minvs,
			maxvs,
			minvs,
			maxvs,
			"307040",opts);
		printf("Gamma log bottom: %s\n", cmdstr);
		i=system(cmdstr);
		if(DoQuery(res_set,"select * from edatalogs where single_plot=1")==0)
		{
			int edatalog_id=0;
			char outFilename_cur[4095];
			while(FetchRow(res_set)) {
				edatalog_id=atoi( FetchField(res_set, "id") );
				sprintf(outFilename_cur,"%s%s.png",outFilename, FetchField(res_set, "label"));
				printf("running single_plot for into %s",outFilename_cur);
				sprintf(cmdstr,
							"./sses_gamma -d %s -r %f -o %s -w %f -h %f -s %f -e %f -pstart %f -pend %f -color %s -rotate -grid -vs -nd %s -single %i",
							dbname,
							// scaleright,
							yfactor,
							outFilename_cur,
							100.0,
							width,
							minvs,
							maxvs,
							minvs,
							maxvs,
							"307040",opts,edatalog_id);
				i=system(cmdstr);
			}
		}

	}

	// printf("controlTot:%.2f lastPrjTot:%.2f lastSvyTot:%.2f\n", controlTot, lastPrjTot, lastSvyTot);
	// fnum=controlTot-lastSvyTot;
	fnum=controlTot-lastPrjTot;
	width*=(100.0/1148);

	sprintf(cmdstr, "./sses_pd -d %s -nd -r %f -T %s -o %s -s %f -e %f -w %f -h %f %s",
		dbname, scaleright, controllogName, outFilename2, mintvd+fnum, maxtvd+fnum, width, height, opts);
	printf("Plot welllog:%s\n", cmdstr);
	i=system(cmdstr);


	fnum=controlTot-lastSvyTot;
	width=130.0;
	sprintf(cmdstr,
		"./sses_gpd  -nr -wlid %ld -r %f -d %s -o %s -cld -wld -s %f -e %f -w %f -h %f -fs %d %s",
		tableid, scaleright, dbname, outFilename3, mintvd+fnum, maxtvd+fnum, width, height, fontsize, opts);
	printf("Plot control log:%s\n", cmdstr);
	i=system(cmdstr);

	return 0 ;
}
