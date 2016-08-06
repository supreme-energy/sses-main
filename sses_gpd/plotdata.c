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

#define GNUPLOT_DEFAULT_FONT	arial

float	dscache_md=0.0;
float	dscache_startmd=0.0;
float	dscache_endmd=0.0;
int	dscache_freeze=0;
int viewdspcnt=0;
int	bUseLogScale=0;
int forms_on = 0;
int bDoControlLog=0;
int bDoWellLog=0;
int idWellLog=-1;
int idCacheStart=-1;
int idCacheEnd=-1;
int bForceNoRotate=0;
float cutinMD=0.0;
float cutoffMD=99999.0;
float hangoffDepth=0.0;
int isghost=0;
int fontSize=9;
int lineWidth=1;
float pointSize=.4;

int bNoDepth=0;
int bNoGrid=0;
int bAutoDepthScale=0;
float startMD, endMD;
float startDepth, endDepth;
float realStart, realEnd;
float plotHeight, plotWidth;
float startmd, endmd;
FILE *outfile;
char cmdstr[65536];
char refwellname[350];

#define MAX_DATAFILES 512
char dataFN[MAX_DATAFILES][L_tmpnam];
int dataCnt[MAX_DATAFILES];
char dataTablename[MAX_DATAFILES][256];
int dataFNcount=0;
int formationFNcount=0;
char selfilename[2][L_tmpnam];
char dscache_filename[4][L_tmpnam];
char pointFN[2][L_tmpnam];
int pointDataCnt[2];
char controlFN[2][L_tmpnam];
char formationFN[8][L_tmpnam];

char totFN[L_tmpnam];
char botFN[L_tmpnam];

float	lastTot=0.0;
float lastBot=0.0;
float tot=0;
float bot=0;
float plantot=0;
float planbot=0;
float controltot=0;
float controlbot=0;

int plotrotate=0;
float plotbias=0;
float plotscale=1;
float bias=0.0;
float scale=1.0;
float lastBias=0.0;
float lastScale=1.0;
float lastRightScale=0;
int lastDataFNcount=0;
float lastValue;
float lastDepth;
int lastDir=99999;
float minvs,maxvs;
float maxtvd, mintvd;

char outFilename[4095];
char dbname[4095];
float leftScale, rightScale;
float topScale, bottomScale;
#define MAXPLOTS 8
char plotstr[1024];
gnuplot_ctrl *gplot;
float softwareVersion = 1.0;
int	keycheckOK = 0;
unsigned char softwareOptions = 0;
unsigned int keySerialNumber = 0;
float minVal, maxVal;

float avg_array[32];
int avg_size=1;
int	avg_count=0;


/*****************************************************************************/

#ifdef USEKEYLOK
void CheckForValidKey(void) {
	fprintf(stderr, "Checking for security key...");
	keycheckOK = CheckForSecurityKeyAccess();
	fprintf(stderr, "%s\n", GetSecurityKeyResult());

	if(keycheckOK) {
		softwareVersion = (float)GetSecurityKeyVersionMajor();
		softwareVersion += (float)GetSecurityKeyVersionMinor() / 100.0f;
		sprintf(cmdstr, "Software version: %.2f", softwareVersion);
		fprintf(stderr, "%s\n", cmdstr);

		softwareOptions = GetSecurityKeyOptionFlags();
		fprintf(stderr, "%s\n", GetSecurityKeyResult());
		keySerialNumber = GetSecurityKeySerialNumber();
		fprintf(stderr, "%s\n", GetSecurityKeyResult());
	}
}
#endif

/*****************************************************************************/
int buildFormationFile(){
	int has_formations = 0;
	if(forms_on == 1){

	int add_id;
	float x,y;
	char q[1024];
	char pltstr[10000];
	char totcolor[128];

	sprintf(q,
			"select * from addforms order by id asc");
	DoQuery(res_set,q);
	while(FetchRow(res_set)){
		has_formations = 1;
		strcpy(totcolor,FetchField(res_set,"color"));
		add_id = atoi(FetchField(res_set,"id"));
		sprintf(q,
				"select thickness from addformsdata where infoid=%i order by md asc",
				add_id,startDepth,endDepth
		);
		printf("%s\n",q);
		DoQuery(res_set2,q);
		FetchRow(res_set2);
		x =  controltot+atof( FetchField(res_set2, "thickness") );
		if(plotrotate) {
			printf("set arrow from %f,%f to %f,%f nohead lc rgb '#%s' lw 3\n",x,leftScale,x,rightScale,totcolor);
			gnuplot_cmd(gplot,"set arrow from %f,%f to %f,%f nohead lc rgb '#%s' lw 3",x,leftScale,x,rightScale,totcolor);
		}else {
				printf("set arrow from %f,%f to %f,%f nohead lc rgb '#%s' lw 3\n",x,leftScale,x,rightScale,totcolor);
				gnuplot_cmd(gplot,"set arrow from %f,%f to %f,%f nohead lc rgb '#%s' lw 3",leftScale,x,rightScale,x,totcolor);
		}



		FreeResult(res_set2);
	}
	}
	return has_formations;
}

void buildControldataFile(char *progname, char* tablename) {
	FILE *out1, *out2;
	int i;
	float x, y;


	sprintf(cmdstr,
		"SELECT md,value FROM \"%s\" WHERE md>=%f AND md<=%f ORDER BY md;",
		tablename, startDepth, endDepth);

	if (DoQuery(res_set, cmdstr))
	{
		fprintf(stderr, "%s: Error in select query for table %s\n", progname, tablename);
		FreeResult(res_set);
		return;
	}
	else
	{
		out1=fopen(dataFN[dataFNcount], "a+");
		out2=fopen(dataFN[dataFNcount+1], "a+");
		if(!out1 || !out2) {
			fprintf(stderr, "%s: failed to open logging data file 2\n", progname);
			FreeResult(res_set);
			exit (-1);
		}
		while(FetchRow(res_set)) {
			x =  atof( FetchField(res_set, "md") );
			y = atof( FetchField(res_set, "value") );
			if(y<minVal)	minVal=y;
			if(y>maxVal)	maxVal=y;

			if(plotrotate) {
				fprintf(out1, "%f %f\n", x, y);
				dataCnt[dataFNcount]++;
				if(rightScale>0) {
					fprintf(out2, "%f %f\n", x, y-rightScale);
					dataCnt[dataFNcount+1]++;
				}
			}
			else {
				fprintf(out1, "%f %f\n", y, x);
				dataCnt[dataFNcount]++;
				if(rightScale>0) {
					fprintf(out2, "%f %f\n", y-rightScale, x);
					dataCnt[dataFNcount+1]++;
				}
			}

			if(x<realStart)	realStart=x;
			if(x>realEnd)	realEnd=x;
		}
		lastRightScale=rightScale;
		lastDataFNcount=dataFNcount;
		lastValue=y;
		lastDepth=x;

		fclose(out1);
		fclose(out2);
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void buildTotBotFiles(char *progname) {
	FILE *botf, *totf;
	float vs;
	int i;
	strcpy(totFN, tmpnam(NULL));
	strcpy(botFN, tmpnam(NULL));
	botf=fopen(botFN, "w+");
	totf=fopen(totFN, "w+");
	if(totf && botf) {
		if(plotrotate) {
			// fprintf(totf, "%f %f\n", plantot, 0.0f);
			// fprintf(botf, "%f %f\n", planbot, 0.0f);
			// fprintf(totf, "%f %f\n", plantot, (float)(rightScale-1));
			// fprintf(botf, "%f %f\n", planbot, (float)(rightScale-1));
			fprintf(totf, "%f %f\n", controltot, 0.0f);
			fprintf(botf, "%f %f\n", controlbot, 0.0f);
			fprintf(totf, "%f %f\n", controltot, (float)(rightScale-1));
			fprintf(botf, "%f %f\n", controlbot, (float)(rightScale-1));
		}
		else {
			// fprintf(totf, "%f %f\n", 0.0f, plantot);
			// fprintf(botf, "%f %f\n", 0.0f, planbot);
			// fprintf(totf, "%f %f\n", (float)(rightScale-1), plantot);
			// fprintf(botf, "%f %f\n", (float)(rightScale-1), planbot);
			fprintf(totf, "%f %f\n", 0.0f, controltot);
			fprintf(botf, "%f %f\n", 0.0f, controlbot);
			fprintf(totf, "%f %f\n", (float)(rightScale-1), controltot);
			fprintf(botf, "%f %f\n", (float)(rightScale-1), controlbot);
		}
		fclose(totf);
		fclose(botf);
	}
}

/*****************************************************************************/

void addLastDataPoint(char *progname, float d, float v) {
	FILE *out1;
	FILE *out2;
	float depth, value;

	if(dataFNcount<=2) {
		// fprintf(stderr, "%s: no previous data set\n", progname);
		return;
	}
	// if(dataFNcount==lastDataFNcount) {
		// return;
	// }
	out1=fopen(dataFN[dataFNcount-2], "a+");
	out2=fopen(dataFN[dataFNcount-2+1], "a+");
	if(!out1 || !out2) {
		fprintf(stderr, "%s: failed to open logging data file 1\n", progname);
		FreeResult(res_set);
		exit (-1);
	}
	depth=d;
	value = (((v-bias)/scale)*lastScale)+lastBias;

	if(plotrotate) {
		fprintf(out1, "%f %f\n", depth, value);
		printf("Add point: %f %f\n", depth, value);
		if(rightScale>0)
			fprintf(out2, "%f %f\n", depth, value-lastRightScale);
	}
	else {
		fprintf(out1, "%f %f\n", value, depth);
		printf("Add point: %f %f\n", value, depth);
		if(rightScale>0)
			fprintf(out2, "%f %f\n", value-lastRightScale, depth);
	}

	fclose(out1);
	fclose(out2);
}

/*****************************************************************************/

void Average(float *y) {
	int i;
	float my;
	if(avg_count<avg_size) {
		for(i=0;i<avg_size;i++)	avg_array[i]=*y;
		avg_count=avg_size;
	}
	for(i=0;i<avg_count-2;i++) avg_array[i]=avg_array[i+1];
	avg_array[avg_count-1] = *y;
	for(i=0,my=0.0;i<avg_count;i++)	my+=avg_array[i];
	*y = my / (float)avg_count;
	avg_array[avg_count-1]=*y;
}

/*****************************************************************************/

void buildLogdataFile(char *progname, char* tablename, int flag, const char *sortdir) {
	FILE *out1, *out2, *pout1, *pout2;
	int i;
	float x, y, md, lastd;
	int hide, datacnt=0;
	int wrapdatacnt=0;

	// cache the tablenames
	strcpy(dataTablename[dataFNcount], tablename);
	strcpy(dataTablename[dataFNcount+1], tablename);

    // sprintf(cmdstr, "SELECT * FROM \"%s\" ORDER BY depth %s;", tablename, sortdir);
	sprintf(cmdstr, "SELECT * FROM \"%s\" ORDER BY md ASC;", tablename);
	if (DoQuery(res_set, cmdstr)) {
		fprintf(stderr, "%s: Error in select query for table %s\n", progname, tablename);
		FreeResult(res_set);
		return;
	} else {
		pout1=fopen(pointFN[0], "a+");
		pout2=fopen(pointFN[1], "a+");
		out1=fopen(dataFN[dataFNcount], "a+");
		out2=fopen(dataFN[dataFNcount+1], "a+");
		if(!out1 || !out2 || !pout1 || !pout2) {
			fprintf(stderr, "%s: failed to open logging data file 3\n", progname);
			FreeResult(res_set);
			exit (-1);
		}
		i=0;
		while(FetchRow(res_set)) {
			x =  atof( FetchField(res_set, "depth") );
			y = atof( FetchField(res_set, "value") );
			md = atof( FetchField(res_set, "md") );
			hide=atoi( FetchField(res_set, "hide") );
			//printf("selected values are x : %f and y: %f \n",x,y);
			//printf("scale: %f and bias %f\n",scale,bias);
			y*=scale;
			y+=bias;
			//printf("plotted values are x : %f and y: %f \n",x,y);
			if(avg_size>0) { Average(&y); }
			if(y<minVal)	minVal=y;
			if(y>maxVal)	maxVal=y;
			if(i>0 && x==lastDepth) {
				i++;
				continue;
			}

			if(plotrotate) {
				if(rightScale>0) {
					fprintf(out2, "%f %f %f\n", x, y-rightScale, md);
					dataCnt[dataFNcount+1]++;
				} // else
				{
					fprintf(out1, "%f %f %f\n", x, y, md);
					dataCnt[dataFNcount]++;
				}
			}
			else {
				if(rightScale>0) {
					fprintf(out2, "%f %f %f\n", y-rightScale, x, md); dataCnt[dataFNcount+1]++;
				} // else
				{
					fprintf(out1, "%f %f %f\n", y, x, md);
					dataCnt[dataFNcount]++;
				}
			}

			// write the point file
			if(hide!=0 && flag!=0) {
				if(plotrotate) {
					fprintf(pout1, "%f %f\n", x, y);
					// printf("Point: %f %f\n", x, y);
				}
				else {
					fprintf(pout1, "%f %f\n", y, x);
					// printf("Point: %f %f\n", y, x);
				}
				pointDataCnt[0]++;
				// printf("pointDataCnt[0]:%d\n", pointDataCnt[0]);

				//write second data file (wrapped)
				if(rightScale>0) {
					if(plotrotate) {
						fprintf(out2, "%f %f\n", x, y-rightScale);
						if(hide!=0 && flag!=0)	fprintf(pout2, "%f %f\n", x, y-rightScale);
					}
					else {
						fprintf(out2, "%f %f\n", y-rightScale, x);
						if(hide!=0 && flag!=0)	fprintf(pout2, "%f %f\n", y-rightScale, x);
					}
					pointDataCnt[1]++;
					// printf("pointDataCnt[1]:%d\n", pointDataCnt[1]);
				}
			}

			if(x<realStart)	realStart=x;
			if(x>realEnd)	realEnd=x;
			lastd=x;
			i++;
			lastDepth=x;
		}
		lastScale=scale;
		lastBias=bias;
		lastRightScale=rightScale;
		lastDataFNcount=dataFNcount;
		lastValue=(y-bias)/scale;

		fclose(out1);
		fclose(out2);
		fclose(pout1);
		fclose(pout2);

		dataCnt[dataFNcount]++;
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void setupPage(void) {
	float n;
	char q[1024];
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
	FreeResult(res_set);
	// not selected normal color
	gnuplot_cmd(gplot, "set style line 1 lt 2 lc rgb '#7070ff' lw %d pt 6 ps %f", lineWidth, pointSize);
	// play with toggles (not used)
	gnuplot_cmd(gplot, "set style line 11 lt 2 lc rgb '#f0a0ff' lw %d pt 6 ps %f", lineWidth, pointSize);
	gnuplot_cmd(gplot, "set style line 12 lt 2 lc rgb '#ffa0a0' lw %d pt 6 ps %f", lineWidth, pointSize);
	// not selected wrap color
	gnuplot_cmd(gplot, "set style line 2 lt 2 lc rgb '#0000b0' lw %d pt 6 ps %f", lineWidth, pointSize);

	// not selected shadow highlight
	gnuplot_cmd(gplot, "set style line 5 lt 2 lc rgb '#000060' lw %d pt 6 ps %f", lineWidth, pointSize);

	// selected color
	gnuplot_cmd(gplot, "set style line 3 lt 2 lc rgb 'green' lw %d pt 6 ps %f", lineWidth, pointSize);
	gnuplot_cmd(gplot, "set style line 303 lt 2 lc rgb 'green' lw %d pt 6 ps %f", lineWidth, pointSize);
	// selected point color
	gnuplot_cmd(gplot, "set style line 4 lt 2 lc rgb 'dark-red' lw %d pt 6 ps %f", lineWidth, pointSize);

	gnuplot_cmd(gplot, "set style line 30 lt 2 lc rgb 'black' lw %d pt 6 ps 1.5 ", lineWidth);

	// TOT and BOT lines
	gnuplot_cmd(gplot, "set style line 31 lt 2 lc rgb '#%s' lw 4 pt 6 ps 1 ",totcolor);
	//gnuplot_cmd(gplot, "set style line 32 lt 2 lc rgb '#d07000' lw 4 pt 6 ps 1 ");

	// control log
	gnuplot_cmd(gplot, "set style line 40 lt 2 lc rgb '#707070' lw %d pt 6 ps 1 ", 2);
	gnuplot_cmd(gplot, "set style line 41 lt 2 lc rgb '#b07070' lw %d pt 6 ps 1 ", 2);
	sprintf(q,
			"\nselect * from addforms order by id asc\n");
	if(!DoQuery(res_set,q)){
		int linestrt = 100;
		int idx = 0;
		while(FetchRow(res_set)){
			strcpy(totcolor,FetchField(res_set,"color"));

			gnuplot_cmd(gplot,"set style line %i lt 2 lc rgb '#%s' lw %d pt 6 ps 1", (linestrt+idx),totcolor,2);

			idx++;

		}
	}
	// grid colors
	gnuplot_cmd(gplot, "set style line 20 lt 2 lc rgb 'black' lw %d ", lineWidth);
	gnuplot_cmd(gplot, "set style line 21 lt 2 lc rgb '#909090' lw %d ", lineWidth);

	if(plotrotate) {
		n=plotHeight;
		plotHeight=plotWidth;
		plotWidth=n;
	}
	gnuplot_cmd(gplot, "set terminal png transparent size %.0f,%.0f font '/usr/share/fonts/truetype/arial.ttf,%d'",
		plotWidth, plotHeight, fontSize);
	// gnuplot_cmd(gplot, "set terminal png small size %.0f,%.0f", plotWidth, plotHeight);
	gnuplot_cmd(gplot, "set output \"%s\"", outFilename);
	gnuplot_cmd(gplot, "set origin 0,0");
	// gnuplot_cmd(gplot, "set size %.0f,%.0f", plotWidth, plotHeight);
	if(!bNoDepth && !plotrotate) {
		gnuplot_cmd(gplot, "set lmargin 1.6");
		gnuplot_cmd(gplot, "set rmargin 1.6");
	}
	if(!bNoDepth && !bNoGrid) {
		gnuplot_cmd(gplot, "set grid xtics mxtics ls 20, ls 21");
		gnuplot_cmd(gplot, "set grid ytics mytics ls 20, ls 21");
	}
}

/*****************************************************************************/

void setScaling(void) {
	char str[256]="\0";
	char str2[256]="\0";
	int i;
	float feetperinch=1.0;

	if(!bNoDepth) {
		gnuplot_cmd(gplot, "set lmargin 1.6");
		gnuplot_cmd(gplot, "set rmargin 1.6");
		// gnuplot_cmd(gplot, "set lmargin at screen .025");
		// gnuplot_cmd(gplot, "set rmargin at screen .975");
	}
	gnuplot_cmd(gplot, "set tmargin 0");
	gnuplot_cmd(gplot, "set bmargin 0");

	if(bAutoDepthScale) {
		if(realStart>startDepth)	startDepth=realStart;
		if(realEnd<endDepth)	endDepth=realEnd;
	}

	sprintf(str, " rotate offset character 2.5 font '/usr/share/fonts/truetype/arial.ttf,%d'", fontSize);
	sprintf(str2, " rotate offset character -1 font '/usr/share/fonts/truetype/arial.ttf,%d'", fontSize);
	// sprintf(str, " rotate offset character 2.5");
	// sprintf(str2, " rotate offset character -1");

	if(!bNoGrid) {
		// just equate an inch to 100 pixels
		feetperinch=fabs(endDepth-startDepth)/plotHeight*100;
		if(feetperinch>80) {
			gnuplot_cmd(gplot, "set ytics 200 %s", str);
			gnuplot_cmd(gplot, "set y2tics 200 %s", str2);
			gnuplot_cmd(gplot, "set mytics 20");
		}
		else if(feetperinch>60) {
			gnuplot_cmd(gplot, "set ytics 100 %s", str);
			gnuplot_cmd(gplot, "set y2tics 100 %s", str2);
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else if(feetperinch>20) {
			gnuplot_cmd(gplot, "set ytics 50 %s", str);
			gnuplot_cmd(gplot, "set y2tics 50 %s", str2);
			gnuplot_cmd(gplot, "set mytics 5");
		}
		else if(feetperinch>16) {
			gnuplot_cmd(gplot, "set ytics 20 %s", str);
			gnuplot_cmd(gplot, "set y2tics 20 %s", str2);
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else if(feetperinch>5) {
			gnuplot_cmd(gplot, "set ytics 10 %s", str);
			gnuplot_cmd(gplot, "set y2tics 10 %s", str2);
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else if(feetperinch>2) {
			gnuplot_cmd(gplot, "set ytics 5 %s", str);
			gnuplot_cmd(gplot, "set y2tics 5 %s", str2);
			gnuplot_cmd(gplot, "set mytics 5");
		}
		else if(feetperinch>1) {
			gnuplot_cmd(gplot, "set ytics 2 %s", str);
			gnuplot_cmd(gplot, "set y2tics 2 %s", str2);
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else if(feetperinch>.5) {
			gnuplot_cmd(gplot, "set ytics 1 %s", str);
			gnuplot_cmd(gplot, "set y2tics 1 %s", str2);
			gnuplot_cmd(gplot, "set mytics 10");
		}
		else {
			gnuplot_cmd(gplot, "set ytics .5 %s", str);
			gnuplot_cmd(gplot, "set y2tics .5 %s", str2);
			gnuplot_cmd(gplot, "set mytics 5");
		}
	}

	if(rightScale<=0) {
		maxVal=ceil(maxVal);
		rightScale=maxVal-((int)maxVal%10)+10;
		if(rightScale<1.0)	rightScale=1.0;
	}
	gnuplot_cmd(gplot, "set format x ''");
	gnuplot_cmd(gplot, "set format x2 ''");

	if(!bUseLogScale) {
		gnuplot_cmd(gplot, "set xrange [%.0f:%.0f]", leftScale, rightScale);
		// gnuplot_cmd(gplot, "set xtics offset 0,.5 %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
		// gnuplot_cmd(gplot, "set x2tics 0,.5 %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
		if(!bNoGrid) {
			gnuplot_cmd(gplot, "set xtics %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
			gnuplot_cmd(gplot, "set x2tics %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
			gnuplot_cmd(gplot, "set mxtics 5");
		}
	} else {
		leftScale=.1;
		gnuplot_cmd(gplot, "set xrange [0.1:%.0f]", rightScale);
		// gnuplot_cmd(gplot, "set xtics 0,10,100");
		if(!bNoGrid) 
			gnuplot_cmd(gplot, "set xtics 0,10");
		gnuplot_cmd(gplot, "show xrange");
		gnuplot_cmd(gplot, "set logscale x");
	}

	gnuplot_cmd(gplot, "set yrange [%.0f:%.0f]", endDepth, startDepth);
	gnuplot_cmd(gplot, "set y2range [%.0f:%.0f]", controltot-endDepth, controltot-startDepth);
	printf("controltot=%f endDepth=%f startDepth=%f\n",controltot,endDepth,startDepth);
	printf("set y2range [%.0f:%.0f]\n", controltot-endDepth, controltot-startDepth);
	// gnuplot_cmd(gplot, "set y2range [%.0f:%.0f]", plantot-endDepth, plantot-startDepth);
	// gnuplot_cmd(gplot, "set y2range [%.0f:%.0f]", lastTot-endDepth, lastTot-startDepth);
}

/*****************************************************************************/

void setScalingRotated(void) {
	char str[256];
	char str2[256];
	int i;
	float n;
	float feetperinch=1.0;

	gnuplot_cmd(gplot, "set lmargin 0");
	gnuplot_cmd(gplot, "set rmargin 0");
	gnuplot_cmd(gplot, "set tmargin 1.2");
	gnuplot_cmd(gplot, "set bmargin 1.2");

	if(bAutoDepthScale) {
		if(realStart>startDepth)	startDepth=realStart;
		if(realEnd<endDepth)	endDepth=realEnd;
	}

	if(!bNoGrid) {
		// just equate an inch to 100 pixels
		feetperinch=fabs(endDepth-startDepth)/plotWidth*100;
		sprintf(str, " font '/usr/share/fonts/truetype/arial.ttf,%d'", fontSize);
		sprintf(str2, " offset character 0,-.5 font '/usr/share/fonts/truetype/arial.ttf,%d'", fontSize);
		// strcpy(str, "\0");
		// sprintf(str2, " offset character 0,-.5");

		if(feetperinch>80) {
			gnuplot_cmd(gplot, "set xtics 200 %s", str);
			gnuplot_cmd(gplot, "set x2tics 200 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 20");
		}
		else if(feetperinch>60) {
			gnuplot_cmd(gplot, "set xtics 100 %s", str);
			gnuplot_cmd(gplot, "set x2tics 100 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else if(feetperinch>20) {
			gnuplot_cmd(gplot, "set xtics 50 %s", str);
			gnuplot_cmd(gplot, "set x2tics 50 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 5");
		}
		else if(feetperinch>16) {
			gnuplot_cmd(gplot, "set xtics 20 %s", str);
			gnuplot_cmd(gplot, "set x2tics 20 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else if(feetperinch>5) {
			gnuplot_cmd(gplot, "set xtics 10 %s", str);
			gnuplot_cmd(gplot, "set x2tics 10 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else if(feetperinch>2) {
			gnuplot_cmd(gplot, "set xtics 5 %s", str);
			gnuplot_cmd(gplot, "set x2tics 5 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 5");
		}
		else if(feetperinch>1) {
			gnuplot_cmd(gplot, "set xtics 2 %s", str);
			gnuplot_cmd(gplot, "set x2tics 2 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else if(feetperinch>.5) {
			gnuplot_cmd(gplot, "set xtics 1 %s", str);
			gnuplot_cmd(gplot, "set x2tics 1 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 10");
		}
		else {
			gnuplot_cmd(gplot, "set xtics .5 %s", str);
			gnuplot_cmd(gplot, "set x2tics .5 %s", str2);
			gnuplot_cmd(gplot, "set mxtics 5");
		}

		/*
		if(plotWidth/(endDepth-startDepth)<.01) {
			gnuplot_cmd(gplot, "set xtics 500 offset character 0");
			gnuplot_cmd(gplot, "set x2tics 500 offset character 0");
			gnuplot_cmd(gplot, "set mxtics 50");
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
		*/
	}

	if(rightScale<=0) {
		maxVal=ceil(maxVal);
		rightScale=maxVal-((int)maxVal%10)+10;
		if(rightScale<1.0)	rightScale=1.0;
	}
	gnuplot_cmd(gplot, "set format y ''");
	gnuplot_cmd(gplot, "set format y2 ''");
	/*
	sprintf(str, "%.0f", rightScale);
	i = strlen(str);
	gnuplot_cmd(gplot, "set label '%.0f' at %f,%f front offset character %d,.5",
		leftScale, leftScale, startDepth, bNoDepth?1:0);
	gnuplot_cmd(gplot, "set label '%.0f' at %f,%f front offset character %d,.5",
		rightScale, rightScale, startDepth, -i);

	gnuplot_cmd(gplot, "set label '%.0f' at %f,%f front offset character %d,-.5",
		leftScale, leftScale, endDepth, bNoDepth?1:0);
	gnuplot_cmd(gplot, "set label '%.0f' at %f,%f front offset character %d,-.5",
		rightScale, rightScale, endDepth, -i);
	*/

	if(bUseLogScale==0) {
		gnuplot_cmd(gplot, "set yrange [%.0f:%.0f]", leftScale, rightScale);
		if(!bNoGrid) {
			gnuplot_cmd(gplot, "set ytics offset 0,.5 %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
			gnuplot_cmd(gplot, "set y2tics 0,.5 %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
			gnuplot_cmd(gplot, "set mytics 5");
		}
	} else {
		leftScale=.1;
		gnuplot_cmd(gplot, "set yrange [0.1:%.0f]", rightScale);
		if(!bNoGrid) 
			gnuplot_cmd(gplot, "set ytics 0,10");
		gnuplot_cmd(gplot, "set logscale y");
		gnuplot_cmd(gplot, "show yrange");
	}

	gnuplot_cmd(gplot, "set xrange [%.0f:%.0f]", startDepth, endDepth);
	gnuplot_cmd(gplot, "set x2range [%.0f:%.0f]", controltot-startDepth, controltot-endDepth);
	// gnuplot_cmd(gplot, "set x2range [%.0f:%.0f]", plantot-startDepth, plantot-endDepth);

}

/*****************************************************************************/

void readAppinfo(char *progname) {
	if (DoQuery(res_set, "SELECT * FROM appinfo;")) {
		fprintf(stderr, "%s: readAppInfo: Error in select query\n", progname);
		CloseDb();
		exit (-1);
	}
	if(FetchRow(res_set)) {
		plotbias=atof(FetchField(res_set, "bias"));
		plotscale=atof(FetchField(res_set, "scale"));
		plotrotate=atof(FetchField(res_set, "viewrotds"));
		viewdspcnt=atof(FetchField(res_set, "viewdspcnt"));
		dscache_md=atof(FetchField(res_set, "dscache_md"));
		dscache_freeze=atoi(FetchField(res_set, "dscache_freeze"));
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void readWelllogInfo(char *progname, char *tn) {
	char str[1024];
	sprintf(str, "SELECT * FROM welllogs WHERE tablename='%s';", tn);
	if (DoQuery(res_set, str)) {
		fprintf(stderr, "%s: readWelllogInfo: Error in select query for table %s\n",
			progname, str);
		return;
	}
	if(FetchRow(res_set)) {
//		controltot=atof(FetchField(res_set, "tot"));
		bias = atof(FetchField(res_set, "scalebias"));
		scale = atof(FetchField(res_set, "scalefactor"));
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void fetchHangoffDepth(char *progname) {
	char str[1024];
	sprintf(str, "SELECT * FROM welllogs ORDER BY md DESC LIMIT 1;");
	if (DoQuery(res_set, str)) {
		fprintf(stderr, "%s: fetchHangoffDepth: Error in select query for table %s\n",
			progname, str);
		return;
	}
	if(FetchRow(res_set)) hangoffDepth=atof(FetchField(res_set, "fault"));
	FreeResult(res_set);
}

/*****************************************************************************/

void barfAndDie(char *progname) {
	printf("Usage: %s -T TablenameOfDataToPlot\n\
	{-d dbname}\n\
	{-s StartDepth}\tdefault: 0\n\
	{-e EndDepth}\tdefault: 99999.0\n\
	{-h Height}\tdefault: 700\n\
	{-w Width}\tdefault: 80\n\
	{-l LeftScale}\tdefault: 0\n\
	{-r RightScale}\tdefault: 0\n\
	{-t TopScale}\tdefault: 0\n\
	{-b BottomScale}\tdefault: 0\n\
	{-ci\tdefault: 0 (Cutin MD)\n\
	{-co\tdefault: 99999.0 (Cutoff MD)\n\
	{-ad\tdefault: 0 (Auto Depth Scaling)\n\
	{-fs\tdefault: 9 (Font size)\n\
	{-lw\tdefault: 1 (Line width)\n\
	{-ps\tdefault: .4 (Point size)\n\
	{-nd}\tdefault: 0 (No Depth tic labels)\n\
	{-nogrid}\tdefault: 0 (No grid)\n\
	{-nr}\tdefault: 0 (No rotate-forced)\n\
	{-cld plot control log data}\n\
	{-wld plot well log data}\n\
	{-avg averageSize}\tdefault:1\n\
	{-wlid well log id selected}\n\
	{-log use logarithmic scale}\n\
	{-o OutputFilename}\tdefault: dataplot.png\n\
", progname);
	exit(1);
}

/*****************************************************************************/

int main(int argc, char * argv[])
{
	int  i, j, hide;
	int ls;
	char buf[1024];
	char whatToPlot[256];
	char oneTablename[256];
	FILE *errout;
	float endd,startd;
	float smd, emd;
	float laststartd, lastendd;
	float xfact;
	endd=startd=0;
	lastendd=laststartd=0;

	keycheckOK=1;
	// CheckForValidKey();
	if(!keycheckOK) {
		printf("Error: No security key found!\n");
		return -1;
	}

	if(argc<2)	barfAndDie(argv[0]);

	startDepth=0.0;
	endDepth=99999.0;
	leftScale=0;
	rightScale=0;
	topScale=0;
	bottomScale=0;
	plotWidth=80;
	plotHeight=700;
	bNoDepth=0;
	bNoGrid=0;
	minVal=maxVal=0;
	strcpy(outFilename, "dataplot.png");
	strcpy(dbname, "\0");
	strcpy(oneTablename, "\0");
	strcpy(whatToPlot, "\0");
	for(i=1; i < argc; i++)
	{
		if(!strcmp(argv[i], "-T"))
			strcpy(whatToPlot, argv[++i]);
		else if(!strcmp(argv[i], "-d"))
			strcpy(dbname, argv[++i]);
		else if(!strcmp(argv[i], "-h"))
			plotHeight=atof(argv[++i]);
		else if(!strcmp(argv[i], "-w"))
			plotWidth=atof(argv[++i]);
		else if(!strcmp(argv[i], "-s"))
			startDepth=atof(argv[++i]);
		else if(!strcmp(argv[i], "-e"))
			endDepth=atof(argv[++i]);
		else if(!strcmp(argv[i], "-o"))
			strcpy(outFilename, argv[++i]);
		else if(!strcmp(argv[i], "-l"))
			leftScale=atof(argv[++i]);
		else if(!strcmp(argv[i], "-r"))
			rightScale=atof(argv[++i]);
		else if(!strcmp(argv[i], "-t"))
			topScale=atof(argv[++i]);
		else if(!strcmp(argv[i], "-b"))
			bottomScale=atof(argv[++i]);
		else if(!strcmp(argv[i], "-fs"))
			fontSize=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-lw"))
			lineWidth=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-ps"))
			pointSize=atof(argv[++i]);
		else if(!strcmp(argv[i], "-co"))
			cutoffMD=atof(argv[++i]);
		else if(!strcmp(argv[i], "-ci"))
			cutinMD=atof(argv[++i]);
		else if(!strcmp(argv[i], "-wlid"))
			idWellLog=atoi(argv[++i]);
		else if(!strcmp(argv[i], "-avg")) {
			avg_size=atoi(argv[++i]);
			if(avg_size<=0)	avg_size=1;
		}
		else if(!strcmp(argv[i], "-nd"))
			bNoDepth=1;
		else if(!strcmp(argv[i], "-nogrid"))
			bNoGrid=1;
		else if(!strcmp(argv[i], "-ad"))
			bAutoDepthScale=1;
		else if(!strcmp(argv[i], "-wld"))
			bDoWellLog=1;
		else if(!strcmp(argv[i], "-cld"))
			bDoControlLog=1;
		else if(!strcmp(argv[i], "-nr"))
			bForceNoRotate=1;
		else if(!strcmp(argv[i], "-log"))
			bUseLogScale=1;
		else if(!strcmp(argv[i], "-aforms"))
			forms_on = 1;
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
		fprintf(stderr, "Failed to open database\n");
		exit(-1);
	}

	for(i=0;i<MAX_DATAFILES;i++) {
		strcpy(dataFN[i], "\0");
		dataCnt[i]=0;
	}
	dataFNcount=-2;
	strcpy(pointFN[0], "\0");
	strcpy(pointFN[1], "\0");
	pointDataCnt[0]=pointDataCnt[1]=0;
	strcpy(selfilename[0], "\0");
	strcpy(selfilename[1], "\0");
	for(i=0;i<4;i++)	strcpy(dscache_filename[i], "\0");
	strcpy(controlFN[0], "\0");
	strcpy(controlFN[1], "\0");
	strcpy(pointFN[0], tmpnam(NULL));
	strcpy(pointFN[1], tmpnam(NULL));
	strcpy(formationFN[0],tmpnam(NULL));
	strcpy(formationFN[1],tmpnam(NULL));
	strcpy(formationFN[2],tmpnam(NULL));
	strcpy(formationFN[3],tmpnam(NULL));
	strcpy(formationFN[4],tmpnam(NULL));
	strcpy(formationFN[5],tmpnam(NULL));
	strcpy(formationFN[6],tmpnam(NULL));
	strcpy(formationFN[7],tmpnam(NULL));
	strcpy(plotstr, "\0");
	realStart=99999;
	realEnd=-99999;
	// startDepth=9990;
	// endDepth=10800;
	// save the one and only dataset to plot
	if(strlen(whatToPlot)) strcpy(oneTablename, whatToPlot);

	readAppinfo(argv[0]);
	if(bForceNoRotate)	plotrotate=0;

	gplot = gnuplot_init();
	if(gplot==NULL) {
		fprintf(stderr, "Failed to create gnuplot_i object\n");
		exit(-1);
	}

	setupPage();


	if (DoQuery(res_set2, "select * from wellinfo")) {
		fprintf(stderr, "%s: Main: Error in select query for table wellinfo\n", argv[0]);
	}
	strcpy(refwellname,"");
	if(FetchRow(res_set2)) {
		plantot=atof(FetchField(res_set2, "tot"));
		planbot=atof(FetchField(res_set2, "bot"));
		strcpy(refwellname,FetchField(res_set2,"refwellname"));
	}
	FreeResult(res_set2);

	// fetchHangoffDepth(argv[0]);

	if(bDoControlLog) {
		sprintf(cmdstr, "select * from controllogs limit 1");
		if (DoQuery(res_set2, cmdstr)) {
			fprintf(stderr, "%s: Main: Error in select query for table %s\n",
				argv[0], cmdstr);
			FreeResult(res_set2);
			CloseDb();
			exit (-1);
		}
		else {
			if(FetchRow(res_set2)) {
				dataFNcount+=2;
				strcpy(dataFN[dataFNcount], tmpnam(NULL));
				strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
				strcpy(whatToPlot, FetchField(res_set2, "tablename"));
				controltot=atof(FetchField(res_set2, "tot"));
				controlbot=atof(FetchField(res_set2, "bot"));
				buildControldataFile(argv[0], whatToPlot);
			}
		}
		FreeResult(res_set2);
		buildTotBotFiles(argv[0]);
	}

	if(!bDoWellLog) {
		if(strlen(oneTablename)) {
			printf("Plot selected only: %s\n", oneTablename);
			dataFNcount+=2;
			strcpy(dataFN[dataFNcount], tmpnam(NULL));
			strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
			strcpy(selfilename[0], dataFN[dataFNcount]);
			strcpy(selfilename[1], dataFN[dataFNcount+1]);
			bias=0.0;
			scale=1.0;
			readWelllogInfo(argv[0], oneTablename);
			bias+=plotbias;
			scale*=plotscale;
			// bias+=rightScale;
			buildLogdataFile(argv[0], oneTablename, 1, "asc");
			// bias-=rightScale;
		}
	}

	// make sure the start/end is in proper order
	if(startDepth>endDepth) {
		endd=startDepth;
		startDepth=endDepth;
		endDepth=endd;
	}
	if(bDoWellLog) {
		// find vs scaling for graphics offsets
		sprintf(cmdstr, "SELECT * FROM welllogs \
			WHERE (startdepth>=%f OR enddepth<=%f) AND startmd>=%f AND endmd<=%f AND hide=0 ORDER BY endmd;",
		startDepth, endDepth, cutinMD, cutoffMD);
		if (DoQuery(res_set2, cmdstr)) {
			fprintf(stderr, "%s: Error in select query for table %s\n",
				argv[0], cmdstr);
			FreeResult(res_set2);
			CloseDb();
			exit (-1);
		}
		else {
			startMD=99999.0; endMD=-9999.0;
			minvs=startd=0;
			endd=maxvs=1;
			while(FetchRow(res_set2)) {
				i = atoi(FetchField(res_set2, "id"));
				startd=atof(FetchField(res_set2, "startvs"));
				endd=atof(FetchField(res_set2, "endvs"));
				smd=atof(FetchField(res_set2, "startmd"));
				emd=atof(FetchField(res_set2, "endmd"));
				isghost=atoi(FetchField(res_set2, "isghost"));
				if(smd<startMD)	startMD=smd;
				if(emd>endMD)	endMD=emd;
				if(startd<minvs)	minvs=startd;
				if(endd<minvs)	minvs=endd;
				if(startd>maxvs)	maxvs=startd;
				if(endd>maxvs)	maxvs=endd;
			}
			xfact=fabs( (rightScale-leftScale)/(maxvs-minvs) );
		}
		FreeResult(res_set2);

		// find the startmd and endmd for the dataset cache
		if(viewdspcnt>0
			// && dscache_freeze!=0
		) {
			sprintf(cmdstr, "SELECT * FROM welllogs WHERE endmd<=%f ORDER BY startmd DESC LIMIT %d;", dscache_md, viewdspcnt);
			if (DoQuery(res_set2, cmdstr)) {
				printf("%s: Error in select query for table %s\n",
					argv[0], cmdstr);
				FreeResult(res_set2);
				CloseDb();
				exit (-1);
			}
			if(FetchRow(res_set2)) {
				dscache_startmd=atof(FetchField(res_set2, "startmd"));
				dscache_endmd=atof(FetchField(res_set2, "endmd"));
				while(FetchRow(res_set2)) { dscache_startmd=atof(FetchField(res_set2, "startmd")); }
			} else viewdspcnt=0;
			FreeResult(res_set2);
		}

		// plot each table
		sprintf(cmdstr, "SELECT * FROM welllogs \
			WHERE (startdepth>=%f OR enddepth<=%f) AND startmd>=%f AND endmd<=%f AND hide=0 ORDER BY endmd;",
		startDepth, endDepth, cutinMD, cutoffMD);
		if (DoQuery(res_set2, cmdstr)) {
			fprintf(stderr, "%s: Error in select query for table %s\n",
				argv[0], cmdstr);
			FreeResult(res_set2);
			CloseDb();
			exit (-1);
		}
		else {
			dataFNcount+=2;
			strcpy(dataFN[dataFNcount], tmpnam(NULL));
			strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
			i=0;
			while(FetchRow(res_set2)) {
				if(i==0) {
					if(endd<startd) lastDir=-1;
					else lastDir=1;
					laststartd=startd; lastendd=endd;
				}
				strcpy(whatToPlot, FetchField(res_set2, "tablename"));
				// printf("Plot: %s: ", whatToPlot);
				i = atoi(FetchField(res_set2, "id"));
				bias = atof(FetchField(res_set2, "scalebias"));
				scale = atof(FetchField(res_set2, "scalefactor"));
				startmd = atof(FetchField(res_set2, "startmd"));
				endmd = atof(FetchField(res_set2, "endmd"));
				startd = atof(FetchField(res_set2, "startdepth"));
				endd = atof(FetchField(res_set2, "enddepth"));
				minvs = atof(FetchField(res_set2, "startvs"));
				maxvs = atof(FetchField(res_set2, "endvs"));
				mintvd = atof(FetchField(res_set2, "starttvd"));
				maxtvd = atof(FetchField(res_set2, "endtvd"));
				lastTot = atof(FetchField(res_set2, "tot"));
				lastBot = atof(FetchField(res_set2, "bot"));
				bias+=plotbias;
				scale*=plotscale;
				// selected dataset
				if(idWellLog>=0 && idWellLog==i) {
					// printf("selected dataset\n");
					if(bDoControlLog) {
						dataFNcount+=2;
						strcpy(dataFN[dataFNcount], tmpnam(NULL));
						strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
					}
					strcpy(selfilename[0], dataFN[dataFNcount]);
					strcpy(selfilename[1], dataFN[dataFNcount+1]);
					if(mintvd<maxtvd) buildLogdataFile(argv[0], whatToPlot, 1, "ASC");
					else buildLogdataFile(argv[0], whatToPlot, 1, "DESC");
					dataFNcount+=2;
					strcpy(dataFN[dataFNcount], tmpnam(NULL));
					strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
				} else if(viewdspcnt>0 &&
					// dscache_freeze!=0 &&
					startmd>=dscache_startmd && 
					strlen(dscache_filename[0])<=0) 
				{
					// printf("start dscache\n");
					if(bDoControlLog) {
						dataFNcount+=2;
						strcpy(dataFN[dataFNcount], tmpnam(NULL));
						strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
					}
					strcpy(dscache_filename[0], dataFN[dataFNcount]);
					strcpy(dscache_filename[1], dataFN[dataFNcount+1]);
					if(mintvd<maxtvd) buildLogdataFile(argv[0], whatToPlot, 0, "ASC");
					else buildLogdataFile(argv[0], whatToPlot, 0, "DESC");
				} else if(viewdspcnt>0 && 
					// dscache_freeze!=0 &&
					startmd>=dscache_endmd && 
					strlen(dscache_filename[2])<=0) 
				{
					// printf("end dscache\n");
					if(bDoControlLog) {
						dataFNcount+=2;
						strcpy(dataFN[dataFNcount], tmpnam(NULL));
						strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
					}
					strcpy(dscache_filename[2], dataFN[dataFNcount]);
					strcpy(dscache_filename[3], dataFN[dataFNcount+1]);
					if(mintvd<maxtvd) buildLogdataFile(argv[0], whatToPlot, 1, "ASC");
					else buildLogdataFile(argv[0], whatToPlot, 1, "DESC");
					dataFNcount+=2;
					strcpy(dataFN[dataFNcount], tmpnam(NULL));
					strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
				} else {
					// printf("Regular dataset\n");
					/*
					// check for change in TVD direction
					if( (startd<endd && laststartd>lastendd) ||	(startd>endd && laststartd<lastendd) ) {
						dataFNcount+=2;
						strcpy(dataFN[dataFNcount], tmpnam(NULL));
						strcpy(dataFN[dataFNcount+1], tmpnam(NULL));
						// plotbias+=10;
					}
					*/
					if(startd<endd) buildLogdataFile(argv[0], whatToPlot, 0, "ASC");
					else	buildLogdataFile(argv[0], whatToPlot, 0, "DESC");
				}
				laststartd=startd;
				lastendd=endd;
				i++;
			}
		}
		FreeResult(res_set2);
	}

	// printf("sd:%f ed:%f\n", startDepth, endDepth);

	if(dataCnt[0]>3 || bDoControlLog) {
	printf("Fancy plot\n");
		// xfact=(rightScale/2.0);
		// gnuplot_cmd(gplot, "color(y) = y >= %f ? (255*65535) : (255*255)", xfact);
		xfact = (endDepth-startDepth)/255;
		// gnuplot_cmd(gplot, "color(y) = ( 10526720 | 255 )");
		// gnuplot_cmd(gplot, "color(y) = ( 10526720 | ( ((y-%f)/%f)&255) )", startMD, xfact);
		gnuplot_cmd(gplot, "color(y) = ( ((y-%f)/%f) )", startDepth, xfact);

		// "color(y) = ( ((127*((y-%f)/%f))*255) | ((127*((y-%f)/%f))*255) | (128+(127*((y-%f)/%f))) )",
			// startDepth, xfact, startDepth, xfact, startDepth, xfact);

		strcpy(cmdstr, "plot ");
		j=0; ls=1;
		for(i=0; strlen(dataFN[i]) && strlen(dataFN[i+1]); i+=2) {
			// check for cached dataset
			if(	viewdspcnt>0 && strcmp(dataFN[i],dscache_filename[0])==0 ) ls=2;
			if(	viewdspcnt>0 && strcmp(dataFN[i],dscache_filename[2])==0 ) ls=1;
			if(bDoControlLog && i==0) { // control log
				// printf("Plot control log\n");
				strcpy(controlFN[0], dataFN[i]);
				if(dataCnt[i]>1) {
					sprintf(plotstr, " '%s' with lines ls 40 t ''", dataFN[i], i+1);
					strcat(cmdstr, plotstr);
					if(rightScale>0 && dataCnt[i+1]>1) {
						strcpy(controlFN[1], dataFN[i+1]);
						sprintf(plotstr, ", '%s' with lines ls 41 t ''", dataFN[i+1], i+2);
						strcat(cmdstr, plotstr);
					}
				}
				else	bDoControlLog=0;
			}
			else if(strcmp(selfilename[0], dataFN[i])==0 || strcmp(selfilename[1], dataFN[i+1])==0) {
				// printf("Selected dataset %d\n", i);		// postpone processing this dataset
			}
			else { // regular datasets w/wrap
				if(dataCnt[i]>3) {
					if(bDoControlLog!=0 || i>0)	strcat(cmdstr, ", ");
					// printf("Plot dataset: %d ls:%d\n", i, ls);
					if(viewdspcnt>0) sprintf(plotstr, " '%s' with lines ls %d t ''", dataFN[i], ls);
					else if(j%2==0) sprintf(plotstr, " '%s' with lines ls 1 t ''", dataFN[i]);
					else sprintf(plotstr, " '%s' with lines ls 2 t ''", dataFN[i]);
					// plot "data.in" using 1:2:(color($2)) with lines linecolor rgb variable 
					// sprintf(plotstr, " '%s' using 1:2:(color($2)) with lines linecolor rgb variable t ''", dataFN[i]);
					strcat(cmdstr, plotstr);
				}
				/* if(rightScale>0 && dataCnt[i+1]>3) {
					// if(dataCnt[i]>1)
						strcat(cmdstr, ", ");
					printf("Plot dataset %d\n", i+1);
					if(j%2!=0) sprintf(plotstr, " '%s' with lines ls 11 t ''", dataFN[i+1]);
					else sprintf(plotstr, " '%s' with lines ls 12 t ''", dataFN[i+1]);
					strcat(cmdstr, plotstr);
				} */
				if(dataCnt[i]>1||dataCnt[i+1]>1)	j++;
			}
		}

		// do the dataset cache again
		if(viewdspcnt>0) {
			for(i=0; strlen(dataFN[i]) && strlen(dataFN[i+1]); i+=2) {
				// check for cached dataset
				if(	strcmp(dataFN[i],dscache_filename[0])==0 ) {
					for(j=i; strlen(dataFN[j]) && strlen(dataFN[j+1]); j+=2) {
						if(	strcmp(dataFN[j],dscache_filename[2])==0 ) break;
						else {
							if(dataCnt[j]>3) {
								if(bDoControlLog!=0 || j>0)	strcat(cmdstr, ", ");
								sprintf(plotstr, " '%s' with lines ls 5 t ''", dataFN[j]);
								strcat(cmdstr, plotstr);
							}
						}
					}
					break;
				}
			}
		}

		// now we plot the selected DS
		if(selfilename[0]>0) {
			// printf("Plot selected dataset\n");
			if(bDoControlLog!=0)	strcat(cmdstr, ",");
			if(isghost==0){
				sprintf(plotstr, " '%s' with lines ls 3 t ''", selfilename[0]);
			} else {
				sprintf(plotstr, " '%s' with lines ls 303 t ''", selfilename[0]);
			}
			strcat(cmdstr, plotstr);
			sprintf(plotstr, ", '%s' with points ls 4 t ''", selfilename[0]);
			strcat(cmdstr, plotstr);
		}

		// points
		if(pointDataCnt[0]>0) {
			// printf("Plot point file 0\n");
			// if(i==0) strcat(cmdstr, ",");
			sprintf(plotstr, ", '%s' with points ls 30 t ''", pointFN[0]);
			strcat(cmdstr, plotstr);
		}
		if(rightScale>0 && pointDataCnt[1]>0) {
			// printf("Plot point file 1\n");
			sprintf(plotstr, ", '%s' with points ls 30 t ''", pointFN[1]);
			strcat(cmdstr, plotstr);
		}

		// TOT and BOT
		if(strlen(totFN) && strlen(botFN)) {
			// printf("Plot TOT/BOT\n");
			sprintf(plotstr, ", '%s' with lines ls 32 t ''", botFN);
			strcat(cmdstr, plotstr);
			sprintf(plotstr, ", '%s' with lines ls 31 t ''", totFN);
			strcat(cmdstr, plotstr);
		}

		// control log - again
		if(bDoControlLog && strlen(controlFN[0])>0) {
			// printf("Plot control log - again\n\n");
			sprintf(plotstr, ", '%s' with lines ls 40 t ''", controlFN[0]);
			strcat(cmdstr, plotstr);
			if(rightScale>0 && strlen(controlFN[1])>0) {
				sprintf(plotstr, ", '%s' with lines ls 41 t ''", controlFN[1]);
				strcat(cmdstr, plotstr);
			}
		}


		if(plotrotate) setScalingRotated();
		else setScaling();

		//check existence of additional formations
		//build add plot files
		if(buildFormationFile()){
			//while(formationFNcount >= 0){
			//	sprintf(plotstr, ", '%s' with lines ls %i t ''", formationFN[formationFNcount],(100+formationFNcount));
			//	strcat(cmdstr,plotstr);
			//	formationFNcount= formationFNcount-1;
			//}
		}
    	gnuplot_cmd(gplot, cmdstr);
		printf("%s\n",cmdstr);
	}
	else {
	printf("Normal plot\n");
		fprintf(stderr, "No data to plot\n");
		setScaling();
		strcpy(dataFN[0], tmpnam(NULL));
		errout=fopen(dataFN[0], "wt");
		if(errout) {
			fprintf(errout, "%f %f\n", 3.1, 10.0);
			fprintf(errout, "%f %f\n", 5.2, 10.1);
			fclose(errout);
		}
		gnuplot_cmd(gplot, "set yrange [10.0:150.0]");
		gnuplot_cmd(gplot, "set xrange [0.1:10000.0]");
		gnuplot_cmd(gplot, "set xtics 0,10,100");
		gnuplot_cmd(gplot, "set logscale x");
		gnuplot_cmd(gplot, "show xrange");
		gnuplot_cmd(gplot, "show yrange");
		sprintf(cmdstr, "plot '%s' with points ls 1 ", dataFN[0]);
    	gnuplot_cmd(gplot, cmdstr);
	}
	CloseDb();
	if(strlen(refwellname)>0){
		printf("set label '%s' at screen %f,%f center norotate front tc rgb 'red'",refwellname,0.0,0.0);
		gnuplot_cmd(gplot,"set label '%s' at screen %f,%f center norotate front tc rgb 'red'",refwellname,0.0,0.0);
	}
	gnuplot_close(gplot);

	for(i=0; i<MAX_DATAFILES; i++) {
		if(access(dataFN[i], R_OK)==0)
			unlink(dataFN[i]);
	}
	if(access(pointFN[0], R_OK)==0) unlink(pointFN[0]);
	if(access(pointFN[1], R_OK)==0) unlink(pointFN[1]);
	if(access(totFN,R_OK)==0) unlink(totFN);
	if(access(botFN,R_OK)==0) unlink(botFN);

	printf("sses_gpd: startDepth: %.2f  endDepth: %.2f   range:%f\n", startDepth, endDepth, endDepth-startDepth);

	return 0 ;
}

