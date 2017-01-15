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

int	bUseLogScale=0;
int bNoDepth;
int bNoDepth2;
int bAutoDepthScale=0;
float startDepth, endDepth;
float realStart, realEnd;
float plotHeight, plotWidth;
FILE *outfile;
char cmdstr[4095];
char dataFN1[L_tmpnam];
char dataFN2[L_tmpnam];
char totFilename[L_tmpnam];
char botFilename[L_tmpnam];
char outFilename[4095];
char dbname[4095];
float leftScale, rightScale;
float topScale, bottomScale;
#define MAXPLOTS 8
char plotstr[MAXPLOTS][1024];
int avgsz;
gnuplot_ctrl *gplot;
float softwareVersion = 1.0;
int	keycheckOK = 0;
unsigned char softwareOptions = 0;
unsigned int keySerialNumber = 0;
float minVal, maxVal;
int wrapDataCnt=0;
int dataCnt=0;

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

void buildLogdataFile(char* tablename) {
	int i;
	FILE *out1;
	FILE *out2;
	int avgcnt=0;
	float avg[256];
	float avgmd[256];
	float x, y;
	char depthname[32];

	dataCnt=wrapDataCnt=0;

	if(strstr(tablename, "cld")>=0 || strstr(tablename, "CLD")>=0) strcpy(depthname, "md");
	else strcpy(depthname, "depth");
	sprintf(cmdstr,
	"SELECT * FROM \"%s\" WHERE %s>=%f AND %s<=%f ORDER BY %s;",
	tablename, depthname, startDepth, depthname, endDepth, depthname);
	if (DoQuery(res_set, cmdstr))
	{
		fprintf(stderr, "plotdata: Error in select query for table %s\n", tablename);
		FreeResult(res_set);
		return;
	}
	else
	{
		out1=fopen(dataFN1, "a+");
		out2=fopen(dataFN2, "a+");
		if(!out1 || !out2) {
			fprintf(stderr, "plotdata: failed to open logging data file 1\n");
			FreeResult(res_set);
			CloseDb();
			exit (-1);
		}
		avgcnt=0;
		while(FetchRow(res_set)) {
			x =  atof( FetchField(res_set, depthname) );
			y = atof( FetchField(res_set, "value") );
			if(avgsz>1) {
				if(avgcnt<avgsz) {
					avg[avgcnt]=y;
					avgmd[avgcnt]=x;
					avgcnt++;
				}
				for(i=0;i<avgcnt-1;i++) {
					avg[i]=avg[i+1];
					avgmd[i]=avgmd[i+1];
				}
				avg[i]=y;
				avgmd[i]=x;
				for(i=0,y=0.0;i<avgcnt;i++) y+=avg[i];
				y/=avgcnt;
			}
			if(y<minVal)	minVal=y;
			if(y>maxVal)	maxVal=y;
			fprintf(out1, "%f %f\n", y, x);
			dataCnt++;
			if(rightScale>0) {
				fprintf(out2, "%f %f\n", y-rightScale, x);
				wrapDataCnt++;
			}

			if(x<realStart)	realStart=x;
			if(x>realEnd)	realEnd=x;
		}
		// printf("%f %f\n", y, x);
		// flush the averaging array
		/* don't need to flush
		printf("%f %f\n", y, x);
		if(avgsz>1) {
			for(i=0;i<2;i++) {	// <avgcnt-1;i++) {
				x = avgmd[i];
				y = (y+avg[i])/2.0;
				fprintf(out1, "%f %f\n", y, x);
				printf("%f %f\n", y, x);
				if(rightScale>0) {
					y-=rightScale;
					fprintf(out2, "%f %f\n", y, x);
				}
			}
		}
		*/
		fclose(out1);
		fclose(out2);
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void buildTotBotFile(char *progname) {
	char q[1024];
	FILE *tout, *bout;
	float x,y;

	sprintf(q, "SELECT * FROM controllogs LIMIT 1;");
	if (DoQuery(res_set, q)) {
		fprintf(stderr, "%s: Main: Error in select query for table %s\n",
			progname, q);
	}
	else {
		if(FetchRow(res_set)) {
			x=atof(FetchField(res_set, "tot"));
			y=atof(FetchField(res_set, "bot"));
			strcpy(totFilename, tmpnam(NULL));
			strcpy(botFilename, tmpnam(NULL));
			tout=fopen(totFilename, "a+");
			bout=fopen(botFilename, "a+");
			fprintf(tout, "%f %f\n", 0.0, x);
			fprintf(tout, "%f %f\n", rightScale, x);
			fprintf(bout, "%f %f\n", 0.0, y);
			fprintf(bout, "%f %f\n", rightScale, y);
			fclose(tout);
			fclose(bout);
		}
	}
	FreeResult(res_set);
}

/*****************************************************************************/

void setStyles(void) {
	char q[1024];
	char totgncmd[1024];
	char totcolor[128];
	sprintf(q,"select * from wellinfo;");
	if(DoQuery(res_set,q)){
		fprintf(stderr,"setStyles: Main: error in colortot select query %s\n",q);
		sprintf(totgncmd,"set style line 3 lt 2 lc rgb '#d00070' lw 3 ");
	}else{
		if(FetchRow(res_set)){
			strcpy(totcolor,FetchField(res_set,"colortot"));
			sprintf(totgncmd,"set style line 3 lt 2 lc rgb '#%s' lw 3 ",totcolor);
		} else {
			sprintf(totgncmd,"set style line 3 lt 2 lc rgb '#d00070' lw 3 ");
		}
	}
	gnuplot_cmd(gplot, "set terminal png size %.0f,%.0f font arial 8", plotWidth, plotHeight);
	gnuplot_cmd(gplot, "set style line 1 lt 2 lc rgb 'red' lw 1 ");
	gnuplot_cmd(gplot, "set style line 2 lt 2 lc rgb 'blue' lw 1 ");
	// tot
	gnuplot_cmd(gplot, totgncmd);
	//bot
	//gnuplot_cmd(gplot, "set style line 4 lt 2 lc rgb '#d07000' lw 3 ");
	gnuplot_cmd(gplot, "set style line 20 lt 2 lc rgb 'black' lw 1 ");
	gnuplot_cmd(gplot, "set style line 21 lt 2 lc rgb '#909090' lw 1 ");

	gnuplot_cmd(gplot, "set grid xtics mxtics ls 20, ls 21");
	gnuplot_cmd(gplot, "set xtics offset 0,.5");
	gnuplot_cmd(gplot, "set x2tics offset 0,.5");

	gnuplot_cmd(gplot, "set grid ytics mytics ls 20, ls 21");
	gnuplot_cmd(gplot, "set tics out scale .2");

	gnuplot_cmd(gplot, "set tmargin 1");
	gnuplot_cmd(gplot, "set bmargin 1");

	if(bNoDepth) gnuplot_cmd(gplot, "set lmargin 0");
	else gnuplot_cmd(gplot, "set lmargin 1");
	if(bNoDepth2) gnuplot_cmd(gplot, "set rmargin 0.2");
	else gnuplot_cmd(gplot, "set rmargin 1.75");
}

/*****************************************************************************/

void setScaling(void) {
	char str[256];
	char str2[256];
	int i;

	if(bAutoDepthScale) {
		if(realStart>startDepth)	startDepth=realStart;
		if(realEnd<endDepth)	endDepth=realEnd;
	}

	sprintf(str, " rotate offset character 2.5 font '/usr/share/fonts/truetype/arial.ttf,8'");
	sprintf(str2, " rotate offset character -1 font '/usr/share/fonts/truetype/arial.ttf,8'");

	if(plotHeight/(endDepth-startDepth)<1) {
		gnuplot_cmd(gplot, "set ytics 500 %s", str);
		gnuplot_cmd(gplot, "set y2tics 500 %s", str2);
		gnuplot_cmd(gplot, "set mytics 10");
	}
	else if(plotHeight/(endDepth-startDepth)<2) {
		gnuplot_cmd(gplot, "set ytics 100 %s", str);
		gnuplot_cmd(gplot, "set y2tics 100 %s", str2);
		gnuplot_cmd(gplot, "set mytics 10");
	}
	else if(plotHeight/(endDepth-startDepth)<4) {
		gnuplot_cmd(gplot, "set ytics 50 %s", str);
		gnuplot_cmd(gplot, "set y2tics 50 %s", str2);
		gnuplot_cmd(gplot, "set mytics 5");
	}
	else if(plotHeight/(endDepth-startDepth)<8) {
		gnuplot_cmd(gplot, "set ytics 10 %s", str);
		gnuplot_cmd(gplot, "set y2tics 10 %s", str2);
		gnuplot_cmd(gplot, "set mytics 5");
	}
	else {
		gnuplot_cmd(gplot, "set ytics 10 %s", str);
		gnuplot_cmd(gplot, "set y2tics 10 %s", str2);
		gnuplot_cmd(gplot, "set mytics 10");
	}

	if(rightScale<=0) {
		maxVal=ceil(maxVal);
		rightScale=maxVal-((int)maxVal%10)+10;
		if(rightScale<1.0)	rightScale=1.0;
	}
	gnuplot_cmd(gplot, "set format x ''");
	gnuplot_cmd(gplot, "set format x2 ''");
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

	if(bUseLogScale==0) {
		gnuplot_cmd(gplot, "set xrange [%.0f:%.0f]", leftScale, rightScale);
		gnuplot_cmd(gplot, "set xtics offset 0,.5 %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
		gnuplot_cmd(gplot, "set x2tics 0,.5 %.0f,%.0f,%.0f", leftScale, (rightScale-leftScale)/2, rightScale);
		gnuplot_cmd(gplot, "set mxtics 5");
	} else {
		gnuplot_cmd(gplot, "set xrange [0.1:%.0f]", rightScale);
		gnuplot_cmd(gplot, "set xtics 0,10");
		gnuplot_cmd(gplot, "show xrange");
		gnuplot_cmd(gplot, "set logscale x");
	}


	gnuplot_cmd(gplot, "set yrange [%.0f:%.0f]", endDepth, startDepth);
	gnuplot_cmd(gplot, "set y2range [%.0f:%.0f]", endDepth, startDepth);
	gnuplot_cmd(gplot, "set output \"%s\"", outFilename);
}

/*****************************************************************************/

void barfAndDie(char *progname) {
	printf("Usage: %s -T TablenameOfDataToPlot\n\
	{-d dbname}\n\
	{-a AmountOfDataToAverage}\tdefault: 1(none)\n\
	{-s StartDepth}\tdefault: 0\n\
	{-e EndDepth}\tdefault: 99999.0\n\
	{-h Height}\tdefault: 700\n\
	{-w Width}\tdefault: 80\n\
	{-l LeftScale}\tdefault: 0\n\
	{-r RightScale}\tdefault: 0\n\
	{-t TopScale}\tdefault: 0\n\
	{-b BottomScale}\tdefault: 0\n\
	{-log use logarithmic scale}\n\
	{-ad\tdefault: 0 (Auto Depth Scaling)\n\
	{-nd}\tdefault: 0 (No Depth tic labels)\n\
	{-nd2}\tdefault: 0 (No Depth on axis 2)\n\
	{-o OutputFilename}\tdefault: dataplot.png\n\
", progname);
	exit(1);
}

/*****************************************************************************/

int main(int argc, char * argv[])
{
    int  i ;
	char buf[1024];
	char whatToPlot[256];
	FILE *errout;

	keycheckOK=1;
	// CheckForValidKey();
	if(!keycheckOK) {
		printf("Error: No security key found!\n");
		return -1;
	}

	if(argc<2)	barfAndDie(argv[0]);

	avgsz=1;
	startDepth=0.0;
	endDepth=99999.0;
	leftScale=0;
	rightScale=0;
	topScale=0;
	bottomScale=0;
	plotWidth=80;
	plotHeight=700;
	bNoDepth=bNoDepth2=0;
	minVal=maxVal=0;
	strcpy(outFilename, "dataplot.png");
	strcpy(dbname, "\0");
	for(i=1; i < argc; i++)
	{
		if(!strcmp(argv[i], "-T"))
			strcpy(whatToPlot, argv[++i]);
		else if(!strcmp(argv[i], "-d"))
			strcpy(dbname, argv[++i]);
		else if(!strcmp(argv[i], "-a"))
			avgsz=atoi(argv[++i]);
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
		else if(!strcmp(argv[i], "-nd"))
			bNoDepth=1;
		else if(!strcmp(argv[i], "-nd2"))
			bNoDepth2=1;
		else if(!strcmp(argv[i], "-ad"))
			bAutoDepthScale=1;
		else if(!strcmp(argv[i], "-log"))
			bUseLogScale=1;
		else barfAndDie(argv[0]);
	}
	if(avgsz<1)	avgsz=1;

	if(strlen(dbname)<=0) {
		barfAndDie(argv[0]);
	}
	if (OpenDb(argv[0], dbname, "umsdata", "umsdata") != 0)
	{
		fprintf(stderr, "Failed to open database\n");
		exit(-1);
	}

	// strcpy(dataFN1, "t.dat");
	strcpy(dataFN1, tmpnam(NULL));
	strcpy(dataFN2, tmpnam(NULL));

	gplot = gnuplot_init();
	if(gplot==NULL) {
		fprintf(stderr, "Failed to create gnuplot_i object\n");
		CloseDb();
		exit(-1);
	}
	setStyles();
	if(strlen(whatToPlot)) {
		realStart=99999;
		realEnd=-99999;
		buildLogdataFile(whatToPlot);
	}
	setScaling();

	buildTotBotFile(argv[0]);

	if(dataCnt>3) {
		for(i=0;i<MAXPLOTS;i++)	plotstr[i][0]='\0';
		i=0;
		sprintf(plotstr[i++], "plot '%s' with lines ls 2 t ''", dataFN1);
		if(rightScale>0 && wrapDataCnt>3)
			sprintf(plotstr[i++], ", '%s' with lines ls 1 t ''", dataFN2);

		if(strlen(totFilename)>0)
			sprintf(plotstr[i++], ", '%s' with lines ls 3 t ''", totFilename);
		if(strlen(botFilename)>0)
			sprintf(plotstr[i++], ", '%s' with lines ls 4 t ''", botFilename);

		for(i=0,cmdstr[0]='\0';i<MAXPLOTS;i++)
			strcat(cmdstr, plotstr[i]);
		fprintf(stderr, "%s\n", cmdstr);
    gnuplot_cmd(gplot, cmdstr);
	}
	else {
		setScaling();
		errout=fopen(dataFN1, "a+");
		if(errout) {
			fprintf(errout, "%f %f\n", 0.0, 0.0);
			fprintf(errout, "%f %f\n", 0.0, 1.0);
			fclose(errout);
		}
		sprintf(cmdstr, "plot '%s' with lines ls 1 t 'no data'", dataFN1);
	    gnuplot_cmd(gplot, cmdstr);
	}

	gnuplot_close(gplot);
	unlink(dataFN1);
	unlink(dataFN2);
	unlink(totFilename);
	unlink(botFilename);
	CloseDb();
	return 0 ;
}
