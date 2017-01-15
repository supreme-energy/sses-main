#include <math.h>
#include <tgmath.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <string.h>

#include "dbio.h"
// #define USEKEYLOK 1
#ifdef USEKEYLOK
#include "../../vendor/keylok/linux/keylok.h"
#endif

FILE *inputfile;
char importfilename[1024];
char dbname[256];
float propazm;
int linecount=0;
char linein[1024];
char lineerr[1024];
int bGotNull=0;

typedef struct t_svy T_SVY;
struct t_svy {
	double depth;
	double inc, azm;
	double tvd, dl, vs, ew, ns;
	double cl;
	double ca, cd;
	double build, turn;
	double temp;
	unsigned long id;
	float tot,bot,dip;
	float fault;
};
#define MAX_SURVEYS	1024
#define	MAX_SURVEYS_PER_CONNECTION	8
T_SVY svy[MAX_SURVEYS];

T_SVY psvy;
T_SVY csvy;

float softwareVersion = 0.0;
int	keycheckOK = 0;
unsigned char softwareOptions = 0;
unsigned int keySerialNumber = 0;
char errorbuf[256];

/*****************************************************************************/

#ifdef USEKEYLOK
void CheckForValidKey(void) {
	log_message("Checking for security key...");
	keycheckOK = CheckForSecurityKeyAccess();
	log_message(GetSecurityKeyResult());

	if(keycheckOK) {
		softwareVersion = (float)GetSecurityKeyVersionMajor();
		softwareVersion += (float)GetSecurityKeyVersionMinor() / 100.0f;
		sprintf(errorbuf, "Software version: %.2f", softwareVersion);
		log_message(errorbuf);

		softwareOptions = GetSecurityKeyOptionFlags();
		log_message(GetSecurityKeyResult());
		keySerialNumber = GetSecurityKeySerialNumber();
		log_message(GetSecurityKeyResult());
	}
}
#endif

/*****************************************************************************/

void log_message(char *message)
{
	FILE *logfile;
	logfile=fopen("/tmp/rdsd.log", "a");
	if(!logfile) return;
	fprintf(logfile,"%s\n",message);
	fclose(logfile);
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

char *trimwhitespace(char *str)
{
	char *end;
	// Trim leading space
	while(isspace(*str)) str++;
	// Trim trailing space
	end = str + strlen(str) - 1;
	while(end > str && isspace(*end)) end--;
	// Write new null terminator
	*(end+1) = 0;
	return str;
}

/*****************************************************************************/

void barfAndDie(char* prog) {
	printf("Usage: %s -f file -d dbname \n", prog);
	exit(1);
}

/*****************************************************************************/

void dieNoCol(char* colname) {
	printf("\nMissing column (%s)\n", colname);
	printf("Offending line (%d): %s\nAborting...\n", linecount, lineerr);
	exit(1);
}

/*****************************************************************************/

void calccurve(T_SVY *pSvy, T_SVY *cSvy, double propazm, int depthunits)
{
	double cl;
	double pInc, cInc, pAzm, cAzm;
	double dogleg, doglegseverity = 0.0;
	double radius = 1.0;

	cl = cSvy->depth - pSvy->depth;
	pInc = radians(pSvy->inc);
	cInc = radians(cSvy->inc);
	pAzm = radians(pSvy->azm);
	cAzm = radians(cSvy->azm);

	dogleg = acos((cos(pInc) * cos(cInc)) + (sin(pInc) * sin(cInc) * cos(cAzm - pAzm)));

	if (cl > 0)
	{
		if(depthunits==1)
			doglegseverity = (dogleg * 30.0) / cl;
		else
			doglegseverity = (dogleg * 100.0) / cl;
	}

	if (dogleg != 0.0)
		radius = (2.0 / dogleg) * tan(dogleg / 2.0);

	cSvy->tvd = pSvy->tvd + ((cl / 2.0) * (cos(pInc) + cos(cInc)) * radius);
	cSvy->ns = pSvy->ns + ((cl / 2.0) * ((sin(pInc) * cos(pAzm)) + (sin(cInc) * cos(cAzm))) * radius);
	cSvy->ew = pSvy->ew + ((cl / 2.0) * ((sin(pInc) * sin(pAzm)) + (sin(cInc) * sin(cAzm))) * radius);

	if (cSvy->ns != 0.0) cSvy->ca = atan2(cSvy->ew, cSvy->ns);
	else cSvy->ca = M_PI_2;

	if (cSvy->ca != 0.0)
		cSvy->cd = fabs(cSvy->ew / sin(cSvy->ca));
	else
		cSvy->cd = cSvy->ns;

	cSvy->vs = cos(cSvy->ca - propazm) * cSvy->cd;
	cSvy->dl = degrees(doglegseverity);
	cSvy->ca = degrees(cSvy->ca);
	if(cSvy->ca < 0.0) cSvy->ca+=360.0;
	cSvy->build = ((cSvy->inc - pSvy->inc) * 100) / cl;
	cSvy->turn = ((cSvy->azm - pSvy->azm) * 100) / cl;
	cSvy->cl = cl;
}

/*****************************************************************************/

void readWellInfo(char *prog) {
	// fetch propsed azimuth from info table
	if (DoQuery(res_set, "SELECT * FROM wellinfo LIMIT 1 OFFSET 0")) {
		fprintf(stderr, "%s: Error in select query for info table", prog);
		exit -1;
	} else {
		if(FetchRow(res_set)) {
			propazm = radians( atof( FetchField(res_set, "propazm") ) );
			// depthunits = atoi( FetchField(res_set, "depthunits") );
		}
		FreeResult(res_set);
	}
}

/*****************************************************************************/

void readLastSurvey(char *prog, float md) {
	char query[256];
	sprintf(query, "SELECT * FROM surveys WHERE plan=0 AND md<%f ORDER BY md DESC LIMIT 1;", md);
	if(DoQuery(res_set, query)) {
		fprintf(stderr, "%s: Error in select query", prog);
		exit -1;
	} else {
		if(FetchRow(res_set)) {
			psvy.id = atol(FetchField(res_set, "id"));
			psvy.depth = atof( FetchField(res_set, "md") );
			psvy.inc = atof( FetchField(res_set, "inc") );
			psvy.azm = atof( FetchField(res_set, "azm") );
			psvy.tvd = atof( FetchField(res_set, "tvd") );
			psvy.ns = atof( FetchField(res_set, "ns") );
			psvy.ew = atof( FetchField(res_set, "ew") );
			psvy.vs = atof( FetchField(res_set, "vs") );
			psvy.ca = atof( FetchField(res_set, "ca") );
			psvy.cd = atof( FetchField(res_set, "cd") );
			psvy.dl = atof( FetchField(res_set, "dl") );
		}
		FreeResult(res_set);
	}
}

/*****************************************************************************/

int main (int argc, char *argv[])
{
	char *tok;
	int i;
	float	startmd, endmd, startvs, endvs, starttvd, endtvd;
	int	svycnt, datacnt;
	float	md, gamma, tvd, vs, inc, azm;
	float	lastmd;
	float dl, n;
	float mdrange;

	if(argc<5) { barfAndDie(argv[0]); }
	strcpy(importfilename, "\0");
	strcpy(dbname, "\0");
	psvy.tvd=psvy.vs=psvy.depth=psvy.inc=psvy.azm=0.0;
	for (i = 1; i < argc; i++) {
		if(!strcmp(argv[i], "-f"))
			strcpy(importfilename, argv[++i]);
		else if(!strcmp(argv[i], "-d"))
			strcpy(dbname, argv[++i]);
		else barfAndDie(argv[0]);
	}
	if(strlen(importfilename)<=0 || strlen(dbname)<=0) {
		barfAndDie(argv[0]);
	}

	// CheckForValidKey();
	// if(!keycheckOK) {
		// CloseDb();
		// printf("Error: No security key found!\n");
		// exit -1;
	// }
	keycheckOK = 1;

	inputfile = fopen(importfilename, "rt");
	if(inputfile == NULL)	return -1;
	svycnt=datacnt=0;

	while ( fgets(linein, 1024, inputfile) != NULL) {
		linecount++;
		strcpy(linein, trimwhitespace(linein));

		// ~Curve section
		if(strcasestr(linein, "~c")) {
			printf("\nData Columns:\n");
			while ( fgets(linein, 1024, inputfile) != NULL) {
				linecount++;
				strcpy(linein, trimwhitespace(linein));
				if(linein[0]=='~')	break;
				if(linein[0]!='#') {
					printf("%s\n", linein);
				}
			}
		}

		// ~ASCII log section
		if(strcasestr(linein, "~a")) {
			startmd=starttvd=startvs=99999.0;
			endmd=endtvd=endvs=-99999.0;
			while ( fgets(linein, 1024, inputfile) != NULL) {
				linecount++;
				strcpy(linein, trimwhitespace(linein));
				strcpy(lineerr, linein);
				if(strlen(linein)<4)	continue;
				if(linein[0]=='~')	break;
				if(isdigit(linein[0])) {
					tok = strtok(linein, " ,\t");
					if(tok==NULL) dieNoCol("md");
					md = atof(tok);

					tok = strtok(NULL, " ,\t");
					if(tok==NULL) dieNoCol("gamma");
					gamma = atof(tok);

					tok = strtok(NULL, " ,\t");
					if(tok==NULL) dieNoCol("tvd");
					tvd = atof(tok);

					tok = strtok(NULL, " ,\t");
					if(tok==NULL) dieNoCol("vs");
					vs = atof(tok);

					tok = strtok(NULL, " ,\t");
					if(tok==NULL) dieNoCol("inc");
					inc = atof(tok);

					tok = strtok(NULL, " ,\t");
					if(tok==NULL) dieNoCol("azm");
					azm = atof(tok);

					if(gamma<0.0) {
						printf("\nWARNING: Found questionable data value (%.2f) at depth (%.2f)...\n",
						gamma, md);
						printf("Offending line in file: %s\n", lineerr);
					}

					if((inc<0.0&&inc>-900.0) ||inc>360.0) {
						printf("\nERROR: Inclination is out of range. Aborting...\n");
						printf("Offending line in file: %s\n", lineerr);
						fclose(inputfile);
						return -1;
					}

					if((azm<0.0&&azm>-900.0) ||azm>360.0) {
						printf("\nERROR: Azimuth is out of range. Aborting...\n");
						printf("Offending line in file: %s\n", lineerr);
						fclose(inputfile);
						return -1;
					}

					// if(md<startmd)	startmd=md;
					// if(md>endmd)	endmd=md;
					// if(tvd<starttvd)	starttvd=tvd;
					// if(tvd>endtvd)	endtvd=tvd;
					// if(vs<startvs)	startvs=vs;
					// if(vs>endvs)	endvs=vs;

					if(svycnt>0) {
						if(md<lastmd) {
							printf("\nERROR: Measured depth has gone backwards. Aborting...\n");
							printf("Offending line in file: %s\n", lineerr);
							fclose(inputfile);
							return -1;
						}
					}

					if(datacnt<=0) {
						startmd=md;
						starttvd=tvd;
						startvs=vs;
					} else {
						endmd=md;
						endtvd=tvd;
						endvs=vs;
					}
					lastmd=md;

					if(inc>=0.0&&azm>=0.0) {
						csvy.depth=svy[svycnt].depth = md;
						csvy.inc=svy[svycnt].inc = inc;
						csvy.azm=svy[svycnt].azm = azm;
						if(svycnt<MAX_SURVEYS-1) svycnt++;
					}

					datacnt++;
				}
			}
/*
			if(starttvd>endtvd) {
				n=starttvd;
				starttvd=endtvd;
				endtvd=n;
			}
			if(startvs>endvs) {
				n=startvs;
				startvs=endvs;
				endvs=n;
			}
*/
			// now print a report out
			mdrange=endmd-startmd;
			printf("\nData lines imported: %d\n", datacnt);
			printf("Depth range: %9.2f\tStep: %5.2f\n", mdrange,  mdrange / (float)(datacnt-1));
			printf("Start MD:  %9.2f\tEnd MD:  %9.2f\n", startmd, endmd);
			printf("Start TVD: %9.2f\tEnd TVD: %9.2f\n", starttvd, endtvd);
			printf("Start VS:  %9.2f\tEnd VS:  %9.2f\n", startvs, endvs);

			if (OpenDb(argv[0], dbname, "umsdata", "umsdata") != 0) {
				fprintf(stderr, "Failed to open database\n");
				exit(-1);
			}
			readWellInfo(argv[0]);
			readLastSurvey(argv[0], endmd);
			CloseDb();

			printf("\nSurvey Information:\n%10s%10s%10s%10s%10s%10s%10s%10s\n",
			"DESC", "DEPTH", "INC", "AZM", "TVD", "VS", "NS", "EW");
			printf("%10s%10.2f%10.2f%10.2f%10.2f%10.2f%10.2f%10.2f\n",
				"Previous:",
				psvy.depth, psvy.inc, psvy.azm, psvy.tvd, psvy.vs, psvy.ns, psvy.ew);

			if( (mdrange/5.0) < svycnt )
			{
				printf("\nERROR: Too many surveys (%d) for depth range. Aborting...\n", svycnt);
				fclose(inputfile);
				return -1;
			}

			for(i=0; i<svycnt; i++) {
				printf("%10s%10.2f%10.2f%10.2f\n",
				"File svy:",
				svy[i].depth, svy[i].inc, svy[i].azm);
			}

			calccurve(&psvy, &csvy, propazm, 0);
			printf("%10s%10.2f%10.2f%10.2f%10.2f%10.2f%10.2f%10.2f\n",
				"Closure:",
				csvy.depth, csvy.inc, csvy.azm, csvy.tvd, csvy.vs, csvy.ns, csvy.ew);

			if(fabs(endtvd-csvy.tvd)>.1) {
				printf("\nERROR: The ending TVD in the LAS file does not match the calculated TVD from the surveys.\n\
Ensure that the survey data is correct\n\
Aborting...\n");
				fclose(inputfile);
				return -1;
			}

			if(fabs(endvs-csvy.vs)>.1) {
				printf("\nERROR: The ending VS in the LAS file does not match the calculated VS from the surveys.\n\
Ensure that the survey data is correct\n\
Aborting...\n");
				fclose(inputfile);
				return -1;
			}
		}
	}
	fclose(inputfile);

	if(datacnt<=1) {
		printf("\nERROR: There is no data in the LAS file or failed to find ~ASCII data section.\n Aborting...\n");
		return -1;
	}

	printf("\nCompleted normally\n");

	return 0;
}
