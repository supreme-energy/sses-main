#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <math.h>

// #define USEKEYLOK 1
#ifdef USEKEYLOK
#include "../../vendor/keylok/linux/keylok.h"
#endif
#include "gnuplot_i.h"
#include "dbio.h"

char edataLabel[256];
char edataColor[256];
char lineColor[256];
float plotStart=-99999.0;
float plotEnd=-99999.0;
int datasetCount=0;
int	bUseLogScale=0;
int bForceNoRotate=0;
int bForceRotate=0;
int bShowGrid=0;
int bNoMargin=0;
int bDoVS=0;
int fontSize=8;
int lineWidth=1;
float pointSize=.4;
int bNoDepth;
int bAutoDepthScale=0;
float depthRange;
float startDepth, endDepth;
float plotHeight, plotWidth;
int wlid=-1;
FILE *outfile;
char cmdstr[65536];

char dataFN2[L_tmpnam];

#define MAX_DATAFILES 1024
char dataFN[MAX_DATAFILES][L_tmpnam];
char dataLabel[MAX_DATAFILES][64];
int dataCnt[MAX_DATAFILES];
int dataID[MAX_DATAFILES];
int dataFNcount=0;
char selfilename[2][L_tmpnam];

typedef struct t_sgta T_SGTA;
struct t_sgta {
	float md;
	float tvd;
	float vs;
	float depth;
	float value;
};
#define MAX_SGTA	65535
T_SGTA	sgta[MAX_SGTA];
int sgtaCount=0;

float dbfault=0.0;
float plotfault=0.0;
float plotdip=0.0;
int plotrotate=0;
float plotbias=0.0;
float plotscale=1.0;
float lastDepth, lastVS, lastTVD;
#define	DIPUP	1
#define DIPDOWN	-1
int lastDir=DIPUP;
float maxtvd, mintvd;
float maxvs, minvs;
float forcedmaxvs, forcedminvs;

char outFilename[4095];
char dbname[4095];
float leftScale, rightScale;
float leftScale2, rightScale2;
float topScale;
#define MAXPLOTS 8
char plotstr[1024];
gnuplot_ctrl *gplot;
float softwareVersion = 1.0;
int	keycheckOK = 0;
unsigned char softwareOptions = 0;
unsigned int keySerialNumber = 0;
float minVal, maxVal;

/*****************************************************************************/

#ifdef USEKEYLOK
void CheckForValidKey(void) {
	printf("Checking for security key...");
	keycheckOK = CheckForSecurityKeyAccess();
	printf("%s\n", GetSecurityKeyResult());

	if(keycheckOK) {
		softwareVersion = (float)GetSecurityKeyVersionMajor();
		softwareVersion += (float)GetSecurityKeyVersionMinor() / 100.0f;
		sprintf(cmdstr, "Software version: %.2f", softwareVersion);
		printf("%s\n", cmdstr);

		softwareOptions = GetSecurityKeyOptionFlags();
		printf("%s\n", GetSecurityKeyResult());
		keySerialNumber = GetSecurityKeySerialNumber();
		printf("%s\n", GetSecurityKeyResult());
	}
}
#endif

/*****************************************************************************/

void setStyles(void) {
	char q[1024];
	char totgncmd[1024];
	char totcolor[128];
	sprintf(q,"select * from wellinfo;");
	if(DoQuery(res_set,q)){
		fprintf(stderr,"setStyles: Main: error in colortot select query %s\n",q);
		strcpy(totcolor,"d00070");
	}else{
		if(FetchRow(res_set)){
			strcpy(totcolor,FetchField(res_set,"colortot"));
		} else {
			strcpy(totcolor,"d00070");
		}
	}
	// selected color
	if(!bDoVS) {
		gnuplot_cmd(gplot, "set style line 1 lt 2 lc rgb '#903000' lw %d pt 6 ps %f", lineWidth, pointSize);
		gnuplot_cmd(gplot, "set style line 2 lt 2 lc rgb '#903000' lw %d pt 6 ps %f", lineWidth, pointSize);
	} else {
		gnuplot_cmd(gplot, "set style line 1 lt 2 lc rgb '#4040ff' lw %d pt 6 ps %f", lineWidth, pointSize);
		gnuplot_cmd(gplot, "set style line 2 lt 2 lc rgb '#4040ff' lw %d pt 6 ps %f", lineWidth, pointSize);
	}

	// not selected color
	gnuplot_cmd(gplot, "set style line 3 lt 2 lc rgb '#%s' lw %d pt 6 ps %f", lineColor, lineWidth, pointSize);

	// selected point color
	gnuplot_cmd(gplot, "set style line 4 lt 2 lc rgb 'dark-red' lw %d pt 6 ps %f", lineWidth, pointSize);

	gnuplot_cmd(gplot, "set style line 30 lt 2 lc rgb 'black' lw %d pt 6 ps 1.5 ", lineWidth);

	// TOT and BOT lines
	gnuplot_cmd(gplot, "set style line 31 lt 2 lc rgb '#%s' lw 4 pt 6 ps 1 ",totcolor);
	//gnuplot_cmd(gplot, "set style line 32 lt 2 lc rgb '#d07000' lw 4 pt 6 ps 1 ");

	gnuplot_cmd(gplot, "set style line 40 lt 2 lc rgb '#707070' lw %d pt 6 ps 1 ", lineWidth);
	gnuplot_cmd(gplot, "set style line 41 lt 2 lc rgb '#b07070' lw %d pt 6 ps 1 ", lineWidth);

	gnuplot_cmd(gplot, "set style line 20 lt 2 lc rgb 'black' lw %d ", lineWidth);
	gnuplot_cmd(gplot, "set style line 21 lt 2 lc rgb '#909090' lw %d ", lineWidth);

	gnuplot_cmd(gplot, "set grid xtics mxtics ls 20, ls 21");
	gnuplot_cmd(gplot, "set xtics offset 0,.5");
	gnuplot_cmd(gplot, "set x2tics offset 0,.5");

	gnuplot_cmd(gplot, "set grid ytics mytics ls 20, ls 21");
	gnuplot_cmd(gplot, "set tics out scale .2");
	if(bNoDepth) {
		gnuplot_cmd(gplot, "set lmargin 2.0");
		gnuplot_cmd(gplot, "set rmargin 2.0");
	}
}

/*****************************************************************************/

void setScaling(void) {
	char str[256];
	int i;
	float diff, r;

	if(!bNoDepth && !bNoMargin) {
		gnuplot_cmd(gplot, "set lmargin 2");
		gnuplot_cmd(gplot, "set rmargin 2.1");
	}
	if(bNoMargin) {
		gnuplot_cmd(gplot, "set lmargin 0");
		gnuplot_cmd(gplot, "set rmargin 0");
	}
	gnuplot_cmd(gplot, "set tmargin 0");
	gnuplot_cmd(gplot, "set bmargin 0");

	if(plotStart>-99990.0 && plotEnd>-99990.0) {
		startDepth=plotStart;
		endDepth=plotEnd;
	}
	else if(bAutoDepthScale || depthRange<=0.0) {
		startDepth=mintvd;
		endDepth=maxtvd;
	}
	else {
		if(bDoVS==0) {
			r=maxtvd-mintvd;
			diff=(depthRange-r)/2.0;
			startDepth=mintvd-diff;
			endDepth=maxtvd+diff;
		} else {
			r=maxvs-minvs;
			diff=(depthRange-r)/2.0;
			startDepth=minvs-diff;
			endDepth=maxvs+diff;
		}
	}

	// if(bShowGrid) {
		if(plotHeight/(endDepth-startDepth)<.01) {
			gnuplot_cmd(gplot, "set ytics 500 rotate offset character 2.5");
			gnuplot_cmd(gplot, "set y2tics 500 rotate offset character -1");
			gnuplot_cmd(gplot, "set mytics 100");
		}
		else if(plotHeight/(endDepth-startDepth)<2) {
			gnuplot_cmd(gplot, "set ytics 100 rotate offset character 2.5");
			gnuplot_cmd(gplot, "set y2tics 100 rotate offset character -1");
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else if(plotHeight/(endDepth-startDepth)<6) {
			gnuplot_cmd(gplot, "set ytics 50 rotate offset character 2.5");
			gnuplot_cmd(gplot, "set y2tics 50 rotate offset character -1");
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else if(plotHeight/(endDepth-startDepth)<8) {
			gnuplot_cmd(gplot, "set ytics 20 rotate offset character 2.5");
			gnuplot_cmd(gplot, "set y2tics 20 rotate offset character -1");
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else if(plotHeight/(endDepth-startDepth)<12) {
			gnuplot_cmd(gplot, "set ytics 10 rotate offset character 2.5");
			gnuplot_cmd(gplot, "set y2tics 10 rotate offset character -1");
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else {
			gnuplot_cmd(gplot, "set ytics 5 rotate offset character 2.5");
			gnuplot_cmd(gplot, "set y2tics 5 rotate offset character -1");
			gnuplot_cmd(gplot, "set mytics 5");
		}
	// }

	gnuplot_cmd(gplot, "set terminal png transparent size %.0f,%.0f font arial %d", plotWidth, plotHeight, fontSize);
	// gnuplot_cmd(gplot, "set terminal png size %.0f,%.0f font arial %d x00ffff", plotWidth, plotHeight, fontSize);
	if(rightScale<=0) {
		maxVal=ceil(maxVal);
		rightScale=maxVal-((int)maxVal%10)+10;
		if(rightScale<1.0)	rightScale=1.0;
	}
	gnuplot_cmd(gplot, "set format x ''");
	gnuplot_cmd(gplot, "set format x2 ''");

	if(!bUseLogScale) {
		gnuplot_cmd(gplot, "set xrange [%.0f:%.0f]", leftScale, rightScale);
		if(!bNoDepth) {
			gnuplot_cmd(gplot, "set xtics offset 0,.5 %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
			gnuplot_cmd(gplot, "set x2tics 0,.5 %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
			gnuplot_cmd(gplot, "set mxtics 5");
		}
	} else {
		leftScale=.1;
		gnuplot_cmd(gplot, "set xrange [0.1:%.0f]", rightScale);
		gnuplot_cmd(gplot, "show xrange");
		gnuplot_cmd(gplot, "set logscale x");
		if(!bNoDepth) gnuplot_cmd(gplot, "set xtics 0,10");
	}
	// for this we'll disable all grids and tics
	if(bNoDepth && !bShowGrid) {
		gnuplot_cmd(gplot, "set noxtics");
		gnuplot_cmd(gplot, "set noytics");
		gnuplot_cmd(gplot, "set noy2tics");
		gnuplot_cmd(gplot, "unset border");
	}

	gnuplot_cmd(gplot, "set yrange [%.1f:%.1f]", endDepth, startDepth);
	gnuplot_cmd(gplot, "set y2range [%.1f:%.1f]", endDepth, startDepth);
	gnuplot_cmd(gplot, "set output \"%s\"", outFilename);
}

/*****************************************************************************/

void setScalingRotated(void) {
	char str[256];
	int i;
	float n;
	float diff, r;

	if(!bDoVS) {
		gnuplot_cmd(gplot, "set lmargin 0");
		gnuplot_cmd(gplot, "set rmargin 0");
	} else {
		gnuplot_cmd(gplot, "set lmargin 1.75");
		gnuplot_cmd(gplot, "set rmargin 1.9");
	}
	if(!bNoMargin && !bNoDepth) {
		gnuplot_cmd(gplot, "set tmargin 1.5");
		gnuplot_cmd(gplot, "set bmargin 1.5");
	} else {
		gnuplot_cmd(gplot, "set tmargin 0");
		gnuplot_cmd(gplot, "set bmargin 0");
	}

	n=plotHeight;
	plotHeight=plotWidth;
	plotWidth=n;

	if(plotStart>-99990.0 && plotEnd>-99990.0) {
		startDepth=plotStart;
		endDepth=plotEnd;
	}
	else if(bAutoDepthScale || depthRange<=0.0) {
		startDepth=mintvd;
		endDepth=maxtvd;
	}
	else {
		if(bDoVS==0) {
			r=maxtvd-mintvd;
			diff=(depthRange-r)/2.0;
			startDepth=mintvd-diff;
			endDepth=maxtvd+diff;
		} else {
			r=maxvs-minvs;
			diff=(depthRange-r)/2.0;
			startDepth=forcedminvs-diff;
			endDepth=forcedmaxvs+diff;
		}
	}
	// startDepth=forcedminvs;//-diff;
	// endDepth=forcedmaxvs;//+diff;

	// if(bShowGrid) {
		if(plotWidth/(endDepth-startDepth)<1) {
			gnuplot_cmd(gplot, "set xtics 500 offset character 0");
			gnuplot_cmd(gplot, "set x2tics 500 offset character 0");
			gnuplot_cmd(gplot, "set mxtics 5");
		}
		else if(plotWidth/(endDepth-startDepth)<2) {
			gnuplot_cmd(gplot, "set xtics 100 offset character 0");
			gnuplot_cmd(gplot, "set x2tics 100 offset character 0");
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else if(plotWidth/(endDepth-startDepth)<6) {
			gnuplot_cmd(gplot, "set xtics 50 offset character 0");
			gnuplot_cmd(gplot, "set x2tics 50 offset character 0");
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else if(plotWidth/(endDepth-startDepth)<8) {
			gnuplot_cmd(gplot, "set xtics 20 offset character 0");
			gnuplot_cmd(gplot, "set x2tics 20 offset character 0");
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else {
			gnuplot_cmd(gplot, "set xtics 10 offset character 0");
			gnuplot_cmd(gplot, "set x2tics 10 offset character 0");
			gnuplot_cmd(gplot, "set mxtics 10");
		}
	// }

	gnuplot_cmd(gplot, "set terminal png transparent size %.0f,%.0f font arial %d", plotWidth, plotHeight, fontSize);
	if(rightScale<=0) {
		maxVal=ceil(maxVal);
		rightScale=maxVal-((int)maxVal%10)+10;
		if(rightScale<1.0)	rightScale=1.0;
	}

	if(!bDoVS) {
		 gnuplot_cmd(gplot, "set format y ''");
		 gnuplot_cmd(gplot, "set format y2 ''");
	}

	if(bUseLogScale==0) {
		gnuplot_cmd(gplot, "set yrange [%.0f:%.0f]", leftScale, rightScale);
		gnuplot_cmd(gplot, "set y2range [%.0f:%.0f]", leftScale2, rightScale2);
		if(bShowGrid) {
			sprintf(str, "%.0f", rightScale);
			gnuplot_cmd(gplot, "set ytics rotate offset 2.5,%d %.0f,%.0f",
				-(strlen(str)/2),
				leftScale,
				rightScale);
			sprintf(str, "%.0f", rightScale2);
			gnuplot_cmd(gplot, "set y2tics rotate offset -1,%d %.0f,%.0f",
				-1, //-(strlen(str)/4),
				leftScale2,
				rightScale2);
			gnuplot_cmd(gplot, "set mytics 5");
		}
	} else {
		leftScale=.1;
		gnuplot_cmd(gplot, "set yrange [0.1:%.0f]", rightScale);
		gnuplot_cmd(gplot, "set logscale y");
		gnuplot_cmd(gplot, "show yrange");
		if(bShowGrid) gnuplot_cmd(gplot, "set ytics 0,10");
	}
	// for this we'll disable all grids and tics
	if(!bShowGrid) {
		gnuplot_cmd(gplot, "unset border");
		gnuplot_cmd(gplot, "set noxtics");
		gnuplot_cmd(gplot, "set nox2tics");
		gnuplot_cmd(gplot, "set noytics");
		gnuplot_cmd(gplot, "set noy2tics");
	}

	gnuplot_cmd(gplot, "set xrange [%.1f:%.1f]", startDepth, endDepth);
	gnuplot_cmd(gplot, "set output \"%s\"", outFilename);
	// gnuplot_cmd(gplot, "set key outside right bottom samplen 2.0 box");
	// gnuplot_cmd(gplot, "set key out vert right top");
	if(bDoVS) {
		gnuplot_cmd(gplot,
			"set label 1 \"%s\" at %f, %f, 0 left front textcolor rgb '#%s' offset character 1, -.7, 0",
			"Gamma", startDepth, rightScale, lineColor);
		gnuplot_cmd(gplot,
			"set label 2 \"%s\" at %f, %f, 0 right front textcolor rgb '#%s' offset character -1, -.7, 0",
			edataLabel, endDepth, rightScale, edataColor);
		// gnuplot_cmd(gplot, "set key default horizontal center top");
	}
}

/*****************************************************************************/

void readAppinfo(char *progname) {
	if (DoQuery(res_set, "SELECT * FROM appinfo;")) {
		printf("%s: readAppInfo: Error in select query\n", progname);
		CloseDb();
		exit (-1);
	}
	if(FetchRow(res_set)) {
		// plotbias=atof(FetchField(res_set, "bias"));
		// plotscale=atof(FetchField(res_set, "scale"));
		if(!bForceRotate)
			plotrotate=atof(FetchField(res_set, "viewrotds"));
	}
	FreeResult(res_set);
}

/*****************************************************************************/

char *readEdataInfo(char *progname) {
	static char str[1024];
	sprintf(str, "SELECT * FROM edatalogs WHERE enabled=1;");
	if (DoQuery(res_set, str)) {
		printf("%s: readEdataInfo: Error in select query for table %s\n",
			progname, str);
		return;
	}
	str[0]=0;
	if(FetchRow(res_set)) {
		strcpy(str, FetchField(res_set, "tablename"));
		strcpy(edataColor, FetchField(res_set, "color"));
		strcpy(edataLabel, FetchField(res_set, "label"));
		leftScale2=atof(FetchField(res_set, "scalelo"));
		rightScale2=atof(FetchField(res_set, "scalehi"));
		gnuplot_cmd(gplot, "set style line 31 lt 2 lc rgb '#%s' lw 1 pt 6 ps 1 ", edataColor);
		if(rightScale>0 && rightScale2>0) {
			plotbias=leftScale2;
			plotscale=rightScale/rightScale2;
		}
	}
	FreeResult(res_set);
	return str;
}

/*****************************************************************************/

void readWelllogInfo(char *progname, char *tn) {
	char str[1024];
	sprintf(str, "SELECT * FROM welllogs WHERE tablename='%s';", tn);
	if (DoQuery(res_set, str)) {
		printf("%s: readWelllogInfo: Error in select query for table %s\n",
			progname, str);
		return;
	}
	if(FetchRow(res_set)) {
		plotbias+=atof(FetchField(res_set, "scalebias"));
		plotscale*=atof(FetchField(res_set, "scalefactor"));
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void readLogdataFile(char *progname, char* tablename, const char *sortdir) {

	sprintf(cmdstr, "SELECT * FROM \"%s\" ORDER BY %s %s;", tablename, "md", sortdir);
	if (DoQuery(res_set, cmdstr)) {
		printf("%s: Error in select query for table %s\n", progname, tablename);
		FreeResult(res_set);
		return;
	}
	while(FetchRow(res_set)) {
		sgta[sgtaCount].md = atof( FetchField(res_set, "md") );
		sgta[sgtaCount].tvd =  atof( FetchField(res_set, "tvd") );
		sgta[sgtaCount].vs = atof( FetchField(res_set, "vs") );
		sgta[sgtaCount].value = atof( FetchField(res_set, "value") );
		sgtaCount++;
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void buildLogdataFile(char *progname, char* tablename) {
	FILE *out1=NULL;
	FILE *out2=NULL;
	int i;
	float value;
	float avg;
	int avgcnt=0;
	float depth, tvd, vs;
	int datacnt=0;
	int wrapdatacnt=0;
	double fdip;

	sprintf(cmdstr, "SELECT * FROM \"%s\" ORDER BY md ASC;", tablename);
	printf("%s: \n", cmdstr);
	if (DoQuery(res_set, cmdstr)) {
		printf("%s: Error in select query for table %s\n", progname, tablename);
		FreeResult(res_set);
		return;
	}
	out1=fopen(dataFN[dataFNcount], "a+");
	out2=fopen(dataFN[dataFNcount+1], "a+");
	if(!out1 || !out2) {
		printf("%s: failed to open logging data file %s\n", progname, dataFN[dataFNcount]);
		FreeResult(res_set);
		exit (-1);
	}
	i=0;
	// plotdip=-.57;
	fdip=plotdip/(180.0/M_PI);
	while(FetchRow(res_set)) {
		value = atof( FetchField(res_set, "value") );
		tvd =  atof( FetchField(res_set, "tvd") );
		vs = atof( FetchField(res_set, "vs") );
		if(value<= -999.25)	continue;
		if(plotscale>0){
			value*=plotscale;
		}
		value+=plotbias;
		// tvd-=plotfault;
		if(value<minVal)	minVal=value;
		if(value>maxVal)	maxVal=value;
		if(i==0 && dataFNcount==0) {
			depth = atof( FetchField(res_set, "depth"))-plotfault;
			lastDepth=depth;
			lastVS=vs;
			lastTVD=tvd;
		}
		if(bDoVS==0) depth=tvd-(-tan(fdip)*fabs(vs-lastVS))-(lastTVD-lastDepth);
		else depth=vs;
		// depth=lastDepth+(-tan(fdip)*(vs-lastVS));
		// depth=tvd-(-tan(fdip)*fabs(vs-lastVS))-(lastTVD-lastDepth);

		if(depth<mintvd)	mintvd=depth; if(depth>maxtvd)	maxtvd=depth;
		if(vs<minvs)	minvs=vs; if(vs>maxvs)	maxvs=vs;
		/* if(depth==lastDepth) 	continue; // same depth */
		// if(i<=1 && dataFNcount==0) {
			// fprintf(out1, "%f %f\n", 0.0, depth+.01); dataCnt[dataFNcount]++;
			// fprintf(out1, "%f %f\n", rightScale*.98, depth+.01); dataCnt[dataFNcount]++;
			// fprintf(out2, "%f %f\n", 0.0, depth); dataCnt[dataFNcount]++;
			// fprintf(out2, "%f %f\n", rightScale*.98, depth); dataCnt[dataFNcount]++;
		// }
		printf("%f %f\n", depth, value);
		if(plotrotate) {
			fprintf(out1, "%f %f\n", depth, value); dataCnt[dataFNcount]++;
			if(rightScale>0) {
				fprintf(out2, "%f %f\n", depth, value-rightScale); dataCnt[dataFNcount+1]++;
			}
		} else {
			fprintf(out1, "%f %f\n", value, depth); dataCnt[dataFNcount]++;
			if(rightScale>0) {
				fprintf(out2, "%f %f\n", value-rightScale, depth); dataCnt[dataFNcount+1]++;
			}
		}
		//lastDepth=depth;
		//lastTVD=tvd;
		//lastVS=vs;
		i++;
	}

	fclose(out1);
	fclose(out2);
	dataCnt[dataFNcount]++;
	FreeResult(res_set);
}

/*****************************************************************************/

void barfAndDie(char *progname) {
	printf("Usage: %s -d dbname\n\
	{-s startmd}\tdefault: 0\n\
	{-e endmd}\tdefault: 99999\n\
	{-pstart plotStart}\tdefault: -1\n\
	{-pend plotEnd}\tdefault: -1\n\
	{-c DScount}\tdefault: 0\n\
	{-h Height}\tdefault: 700\n\
	{-w Width}\tdefault: 80\n\
	{-r RightScale}\tdefault: 0\n\
	{-t TopScale}\tdefault: 0\n\
	{-wlid id}\tdefault: none\n\
	{-range depthRange}\tdefault: auto scale\n\
	{-bias Bias}\tdefault: 0.0\n\
	{-scale Scale}\tdefault: 1.0\n\
	{-dip Dip}\tdefault: 0.0\n\
	{-fault Fault}\tdefault: 0.0\n\
	{-ad\tdefault: 0 (Auto Depth Scaling)\n\
	{-nd}\tdefault: 0 (No Depth tic labels)\n\
	{-nr}\tdefault: 0 (No rotate-forced)\n\
	{-rotate}\tdefault: 0 (rotate-forced)\n\
	{-grid}\tdefault: 0 (show grid)\n\
	{-color}\tdefault: #4040ff (line color)\n\
	{-nomargin}\tdefault: 0 (show margin)\n\
	{-log use logarithmic scale}\n\
	{-vs plot by vs}\n\
	{-o OutputFilename}\tdefault: dataplot.png\n\
", progname);
	exit(1);
}

/*****************************************************************************/

int main(int argc, char * argv[])
{
	int  i, id;
	char buf[1024];
	char whatToPlot[256];
	char oneTablename[256];
	FILE *errout;
	float startmd, endmd;
	float tvd, depth;
	float starttvd, endtvd, startvs, endvs;

	keycheckOK=1;
	// CheckForValidKey();
	if(!keycheckOK) {
		printf("Error: No security key found!\n");
		return -1;
	}

	if(argc<2)	barfAndDie(argv[0]);

	startmd=0.0;
	endmd=99999.0;
	startDepth=0.0;
	depthRange=-1;
	endDepth=100.0;
	leftScale=leftScale2=0;
	rightScale=rightScale2=0;
	topScale=0;
	plotWidth=80;
	plotHeight=700;
	bNoDepth=0;
	minVal=maxVal=0;
	strcpy(outFilename, "dataplot.png");
	strcpy(dbname, "\0");
	strcpy(oneTablename, "\0");
	strcpy(whatToPlot, "\0");
	strcpy(lineColor, "ff7000");
	for(i=1; i < argc; i++)
	{
		if(!strcmp(argv[i], "-d"))
			strcpy(dbname, argv[++i]);
		else if(!strcmp(argv[i], "-c"))
			datasetCount=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-h"))
			plotHeight=atof(argv[++i]);
		else if(!strcmp(argv[i], "-s"))
			startmd=atof(argv[++i]);
		else if(!strcmp(argv[i], "-e"))
			endmd=atof(argv[++i]);
		else if(!strcmp(argv[i], "-w"))
			plotWidth=atof(argv[++i]);
		else if(!strcmp(argv[i], "-o"))
			strcpy(outFilename, argv[++i]);
		else if(!strcmp(argv[i], "-l"))
			leftScale=leftScale2=atof(argv[++i]);
		else if(!strcmp(argv[i], "-r"))
			rightScale=rightScale2=atof(argv[++i]);
		else if(!strcmp(argv[i], "-t"))
			topScale=atof(argv[++i]);
		else if(!strcmp(argv[i], "-wlid"))
			wlid=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-bias"))
			plotbias=atof(argv[++i]);
		else if(!strcmp(argv[i], "-scale"))
			plotscale=atof(argv[++i]);
		else if(!strcmp(argv[i], "-dip"))
			plotdip=atof(argv[++i]);
		else if(!strcmp(argv[i], "-fault"))
			plotfault=atof(argv[++i]);
		else if(!strcmp(argv[i], "-range"))
			depthRange=atof(argv[++i]);
		else if(!strcmp(argv[i], "-pstart"))
			plotStart=atof(argv[++i]);
		else if(!strcmp(argv[i], "-pend"))
			plotEnd=atof(argv[++i]);
		else if(!strcmp(argv[i], "-fs"))
			fontSize=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-lw"))
			lineWidth=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-ps"))
			pointSize=atof(argv[++i]);
		else if(!strcmp(argv[i], "-nd"))
			bNoDepth=1;
		else if(!strcmp(argv[i], "-ad"))
			bAutoDepthScale=1;
		else if(!strcmp(argv[i], "-nr"))
			bForceNoRotate=1;
		else if(!strcmp(argv[i], "-rotate"))
			plotrotate=bForceRotate=1;
		else if(!strcmp(argv[i], "-grid"))
			bShowGrid=1;
		else if(!strcmp(argv[i], "-nomargin"))
			bNoMargin=1;
		else if(!strcmp(argv[i], "-log"))
			bUseLogScale=1;
		else if(!strcmp(argv[i], "-vs"))
			bDoVS=1;
		else if(!strcmp(argv[i], "-color"))
			strcpy(lineColor, argv[++i]);
		else {
			printf("Error in parameter: %s\n", argv[i]);
			barfAndDie(argv[0]);
		}
	}

	if(strlen(dbname)<=0) {
		barfAndDie(argv[0]);
	}
	if (OpenDb(argv[0], dbname, "umsdata", "umsdata") != 0)
	{
		printf("Failed to open database\n");
		exit(-1);
	}

	gplot = gnuplot_init();
	if(gplot==NULL) {
		printf("Failed to create gnuplot_i object\n");
		exit(-1);
	}
	setStyles();

	for(i=0;i<MAX_DATAFILES;i++) {
		strcpy(dataFN[i], "\0");
		dataCnt[i]=0;
	}
	dataFNcount=0;
	strcpy(selfilename[0], "\0");
	strcpy(selfilename[1], "\0");
	strcpy(plotstr, "\0");

			forcedminvs=startmd;
			forcedmaxvs=endmd;

	readAppinfo(argv[0]);
	if(bForceNoRotate)	plotrotate=0;

	// make sure the start/end is in proper order
	if(startDepth>endDepth) {
		depth=startDepth;
		startDepth=endDepth;
		endDepth=depth;
	}
	// if(depthRange>0) {
		// startmd=endmd-depthRange;
	// }
	// if(startmd>endmd) {
		// depth=startmd;
		// startmd=endmd;
		// endmd=depth;
	// }

	// printf("Start:%.2f End:%.2f Range:%.2f\n", startmd, endmd, depthRange);

	// find vs scaling for graphics offsets
	if(datasetCount>0) {
		// printf("SELECT * FROM welllogs WHERE endmd<=%f ORDER BY startmd DESC LIMIT %d;", endmd, datasetCount);
		sprintf(cmdstr, "SELECT * FROM welllogs WHERE endmd<=%f ORDER BY startmd DESC LIMIT %d;", endmd, datasetCount);
	}
	else {
		if(!bDoVS) 
			sprintf(cmdstr, "SELECT * FROM welllogs WHERE startmd>=%f AND endmd<=%f ORDER BY startmd;", startmd, endmd);
		else
			sprintf(cmdstr, "SELECT * FROM welllogs WHERE startvs>=%f AND endvs<=%f ORDER BY startmd;", startmd, endmd);
	}
	if (DoQuery(res_set2, cmdstr)) {
		printf("%s: Error in select query for table %s\n",
			argv[0], cmdstr);
		FreeResult(res_set2);
		CloseDb();
		exit (-1);
	}
	if(FetchRow(res_set2)) {
		startmd=atof(FetchField(res_set2, "startmd"));
		endmd=atof(FetchField(res_set2, "endmd"));
		dbfault=atof(FetchField(res_set2, "fault"));
		// printf("startmd:%.2f endmd:%.2f dbfault:%.2f\n", startmd, endmd, dbfault);

		tvd=atof(FetchField(res_set2, "starttvd"));
		depth=atof(FetchField(res_set2, "startdepth"));
		dbfault=atof(FetchField(res_set2, "fault"));
		if(!bDoVS) {
			while(FetchRow(res_set2)) {
				startmd=atof(FetchField(res_set2, "startmd"));
				tvd=atof(FetchField(res_set2, "starttvd"));
				depth=atof(FetchField(res_set2, "startdepth"));
				dbfault=atof(FetchField(res_set2, "fault"));
				// printf("shadow: starttvd: %.2f depth: %.2f\n", tvd, depth);
			}
		} else {
			while(FetchRow(res_set2)) {
				endmd=atof(FetchField(res_set2, "endmd"));
				tvd=atof(FetchField(res_set2, "starttvd"));
				depth=atof(FetchField(res_set2, "startdepth"));
				dbfault=atof(FetchField(res_set2, "fault"));
				// printf("bDoVS: starttvd: %.2f depth: %.2f\n", tvd, depth);
			}
		}
		if(plotStart>=0.0 && plotEnd>0.0) {
			//plotfault+=(tvd-depth);
			// printf("tvd:%.2f depth:%.2f plotfault:%.2f\n", tvd, depth, plotfault);
		}
	}
	FreeResult(res_set2);

	// printf("startmd:%.2f endmd:%.2f dbfault:%.2f\n", startmd, endmd, dbfault);

	// plot each table
	// sprintf(cmdstr, "SELECT * FROM welllogs ORDER BY startmd DESC LIMIT %d;", datasetCount);
	sprintf(cmdstr, "SELECT * FROM welllogs WHERE startmd>=%.2f AND endmd<=%.2f ORDER BY startmd ASC;", startmd, endmd);
	if (DoQuery(res_set2, cmdstr)) {
		printf("%s: Error in select query for table %s\n", argv[0], cmdstr);
		FreeResult(res_set2);
		CloseDb();
		exit (-1);
	}
	minvs=mintvd=99999.0;
	maxvs=maxtvd=-99999.0;
	i=0;
	dataFNcount=0;
	strcpy(dataFN[dataFNcount], tmpnam(NULL));
	strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
	while(FetchRow(res_set2)) {
		strcpy(whatToPlot, FetchField(res_set2, "tablename"));
		id = atoi(FetchField(res_set2, "id"));
		starttvd = atof(FetchField(res_set2, "starttvd"));
		endtvd = atof(FetchField(res_set2, "endtvd"));
		startvs = atof(FetchField(res_set2, "startvs"));
		endvs = atof(FetchField(res_set2, "endvs"));
		if(i==0) { lastVS=startvs; lastDepth=lastTVD=starttvd; }
		if( (wlid==id && i>0) && !bDoVS ) {	// minimum 2 files
			dataFNcount+=2;
			strcpy(dataFN[dataFNcount], tmpnam(NULL));
			strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
		}
		dataID[dataFNcount]=id;
		dataID[dataFNcount+1]=id;
		buildLogdataFile(argv[0], whatToPlot);
		if( (wlid==id || i==0) && !bDoVS)	// a third file if selected id falls in between
		{
			dataFNcount+=2;
			strcpy(dataFN[dataFNcount], tmpnam(NULL));
			strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
		}
		// printf("starttvd:%.2f endtvd:%.2f\n", starttvd, endtvd);
		// printf("lastVS:%.2f lastTVD:%.2f lastDepth:%.2f\n", lastVS, lastTVD, lastDepth);
		i++;
	}
	dataFNcount+=2;
	FreeResult(res_set2);

	if(bDoVS) {
		strcpy( oneTablename, readEdataInfo(argv[0]) );
		if(strlen(oneTablename)) {
			strcpy(dataFN[dataFNcount], tmpnam(NULL));
			strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
			buildLogdataFile(argv[0], oneTablename);
			dataID[dataFNcount]=-9;
			dataFNcount+=2;
		}
	}

	CloseDb();

	if(dataCnt[0]>0) {
		strcpy(cmdstr, "plot ");
		for(i=0; strlen(dataFN[i]); i+=2) {
			if(dataCnt[i]>1) {
				if(i>0)	strcat(cmdstr, ",");
				if(wlid!=dataID[i]) {
					if(dataID[i]!=-9) {
						// if(bDoVS) sprintf(plotstr, " '%s' with lines ls 3 t 'Gamma'", dataFN[i]);
						// else
							sprintf(plotstr, " '%s' with lines ls 3 t ''", dataFN[i]);
					} else {
						// if(bDoVS) sprintf(plotstr, " '%s' with lines ls 31 t '%s'", dataFN[i], edataLabel);
						// else
							sprintf(plotstr, " '%s' with lines ls 31 t ''", dataFN[i]);
					}
				}
				else {
					// printf("Plot selected data (%d)\n", dataCnt[i]);
					sprintf(plotstr, " '%s' with lines ls 1 t ''", dataFN[i]);
				}
				strcat(cmdstr, plotstr);
				/* disable wrapping for the data cache
				if(rightScale>0 && dataCnt[i+1]>1) {
					sprintf(plotstr, ", '%s' with lines ls 3 t ''", dataFN[i+1]);
					strcat(cmdstr, plotstr);
				} */
			}
		}
		if(plotrotate) setScalingRotated();
		else setScaling();
		// printf("cmdstr:%s\n", cmdstr);
    	gnuplot_cmd(gplot, cmdstr);
	}
	else {
		printf("No data to plot\n");
		if(plotrotate) setScalingRotated();
		else setScaling();
		strcpy(dataFN[0], tmpnam(NULL));
		errout=fopen(dataFN[0], "wt");
		if(errout) {
			fprintf(errout, "%f %f\n", 3.1, 10.0);
			fprintf(errout, "%f %f\n", 5.2, 10.1);
			fclose(errout);
		}
		// gnuplot_cmd(gplot, "set yrange [10.0:150.0]");
		// gnuplot_cmd(gplot, "set xrange [0.1:10000.0]");
		// gnuplot_cmd(gplot, "set xtics 0,10,100");
		// gnuplot_cmd(gplot, "set logscale x");
		// gnuplot_cmd(gplot, "show xrange");
		// gnuplot_cmd(gplot, "show yrange");
		sprintf(cmdstr, "plot '%s' with points ls 1 ", dataFN[0]);
    	gnuplot_cmd(gplot, cmdstr);
	}
	gnuplot_close(gplot);
	for(i=0; i<MAX_DATAFILES; i++) if(strlen(dataFN[i])>0) unlink(dataFN[i]);
	// printf("sses_dsp: startmd: %.2f  endmd: %.2f\n", startmd, endmd);
	// printf("sses_dsp: mintvd: %.2f  maxtvd: %.2f\n", mintvd, maxtvd);
	// printf("sses_dsp: minvs: %.2f  maxvs: %.2f\n", minvs, maxvs);
	// printf("startDepth:%.2f endDepth:%.2f range:%.2f fault:%.2f\n", startDepth, endDepth, endDepth-startDepth, plotfault);
	// printf("dbfault:%.2f\n", dbfault);
	return 0 ;
}

