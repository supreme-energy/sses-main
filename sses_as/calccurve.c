#include <math.h>
#include <tgmath.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <string.h>
#include <limits.h>

// #include "mysql.h"
// #include "rdssql.h"
#include "dbio.h"
// #include "../../vendor/keylok/linux/keylok.h"

float fStartTot=0.0;
float fStartBot = 0.0;
float fStartDip = 0.0;
float fStartFault = 0.0;

float fOffsetMD=0.0;
float fOffsetTVD=0.0;
float fOffsetVS=0.0;

ResultSet resultSetInfo;
ResultSet *res_setinfo = &resultSetInfo;
ResultSet resultSetIn;
ResultSet *res_setin = &resultSetIn;
ResultSet resultSetOut;
ResultSet *res_setout = &resultSetOut;

typedef struct t_svy T_SVY;
struct t_svy {
	unsigned long id;
	double depth, inc, azm;
	double tvd, vs;
	double adjtvd, adjvs;
	double ew, ns;
	double cl, ca, cd, dl;
	double build, turn;
	double temp;
	float tot,bot,dip,fault;
	float tpos, bpos;
};

int verbose = 1;
char errorbuf[4095];

float softwareVersion = 0.0;
int	keycheckOK = 0;
unsigned char softwareOptions = 0;
unsigned int keySerialNumber = 0;

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

float getSectionTot(float prevdepth, float currdepth) {
	char q[256];
	int i;
	float n=0;

	sprintf(q, "SELECT * FROM welllogs WHERE startvs<=%f AND endvs>=%f;", prevdepth, currdepth);
	printf("%s\n", q);
	if(DoQuery(res_set3, q))
		return 0.0;
	for(i=0; FetchRow(res_set3); i++) {
		n=atof(FetchField(res_set3, "tot"));
		printf("tot:%f\n", n);
	}
	FreeResult(res_set3);
	return n;
}

/*****************************************************************************/

float getSectionFault(float prevdepth, float currdepth) {
	char q[256];
	int i;
	float n=0;

	sprintf(q, "SELECT * FROM welllogs WHERE startvs<=%f AND endvs>=%f;", currdepth, currdepth);

	if(DoQuery(res_set3, q))
		return 0.0;

	for(i=0; FetchRow(res_set3); i++) {
		n+=atof(FetchField(res_set3, "fault"));
	}
	FreeResult(res_set3);
	return n;
}

/*****************************************************************************/

float getSectionDip(float prevdepth, float currdepth) {
	char q[256];
	int i;
	float n=0;
	float dip, startmd, endmd;

	sprintf(q, "SELECT * FROM welllogs WHERE endvs>=%f ORDER BY endmd DESC LIMIT 1;", currdepth);
	if(DoQuery(res_set3, q)) return 0.0;
	for(i=0; FetchRow(res_set3); i++) {
		dip=atof(FetchField(res_set3, "dip"));
		n=-dip;
		printf("survey dip:%f\n", dip);
	}
	FreeResult(res_set3);

	if(i<=0) {
		sprintf(q, "SELECT * FROM surveys WHERE plan=1 AND vs>=%f ORDER BY md DESC LIMIT 1;", currdepth);
		if(DoQuery(res_set3, q)==0) {
			for(i=0; FetchRow(res_set3); i++) {
				dip=atof(FetchField(res_set3, "dip"));
				n=dip;
			}
			FreeResult(res_set3);
		}
	}
	
	if(i<=0) {
		sprintf(q, "SELECT * FROM projections WHERE vs>%f ORDER BY md ASC LIMIT 1", currdepth);
		if(DoQuery(res_set3, q)==0) {
			for(i=0; FetchRow(res_set3); i++) {
				dip=atof(FetchField(res_set3, "dip"));
				n=dip;
			}
			FreeResult(res_set3);
		}
	}

	if(i<=0)	return 0.0;
	// n/=(float)i;
	// printf("Dip:%f\n",n);
	return n;
}

/*****************************************************************************/

void barfAndDie(char* prog) {
	printf("Usage: %s -d <dbname>\n", prog);
	exit(1);
}

/*****************************************************************************/

int main (int argc, char *argv[])
{
	int i, j;
	long	infoid;
	T_SVY psvy, csvy;
	char query[4095];
	char dbname[256];

	strcpy(dbname, "\0");
	for (i = 1; i < argc; i++)
    {
		if (!strcmp (argv[i], "-d")) strcpy(dbname, argv[++i]);
	}
	if(strlen(dbname)<=0) {
		barfAndDie(argv[0]);
	}

	if (OpenDb(argv[0], dbname, "umsdata", "umsdata") != 0)
	{
		fprintf(stderr, "Failed to open database\n");
		exit(-1);
	}

	// CheckForValidKey();
	// if(!keycheckOK) {
		// CloseDb();
		// printf("Error: No security key found!\n");
		// exit -1;
	// }
	keycheckOK = 1;

	if (DoQuery(res_setinfo, "SELECT * FROM addforms")) {
		fprintf(stderr, "argv[0]: Error in select query for info table");
		return -1;
	} else {
		while (FetchRow(res_setinfo)) {
			infoid = atol( FetchField(res_setinfo, "id") );
			fOffsetMD = atof( FetchField(res_setinfo, "offsetmd") );
			fOffsetTVD = atof( FetchField(res_setinfo, "offsettvd") );
			fOffsetVS = atof( FetchField(res_setinfo, "offsetvs") );
			fStartTot = atof( FetchField(res_setinfo, "tot") );
			fStartBot = atof( FetchField(res_setinfo, "bot") );
			fStartDip = radians( atof( FetchField(res_setinfo, "dip") ) );
			fStartFault = atof( FetchField(res_setinfo, "fault") );
			// cycle through surveys
			sprintf(query, "SELECT * FROM addformsdata WHERE infoid=%ld ORDER BY md ASC;", infoid);
			if(DoQuery(res_setin, query)) {
				fprintf(stderr, "argv[0]: Error in select query");
				return -1;
			} else {
				i=0;
				DoQuery(res_setout, "BEGIN TRANSACTION;");
				while(FetchRow(res_setin)) {
					if(i==0) {
						psvy.id = atol(FetchField(res_setin, "id"));
						psvy.depth = atof( FetchField(res_setin, "md") );
						psvy.tvd = atof( FetchField(res_setin, "tvd") );
						psvy.vs = atof( FetchField(res_setin, "vs") );
						psvy.tot = fStartTot;
						psvy.bot = fStartBot;
						psvy.dip=0.0;
						psvy.fault=0.0;
						// psvy.dip = fStartDip;
						// psvy.fault = fStartFault;
						psvy.adjtvd=psvy.tvd-fOffsetTVD;
						psvy.adjvs=psvy.vs-fOffsetVS;
						psvy.tot+=psvy.fault;
						psvy.bot+=psvy.fault;
						if(keycheckOK) {
							psvy.dip=(getSectionDip(psvy.vs-fOffsetVS, psvy.vs-fOffsetVS));
							// psvy.tot=fStartTot+(-tan(psvy.dip)*(csvy.vs-psvy.vs));
							// psvy.bot=fStartBot+(-tan(psvy.dip)*(csvy.vs-psvy.vs));
						}
						psvy.tot+=fStartFault; psvy.bot+=fStartFault;
						sprintf(query, "UPDATE addformsdata set adjtvd=%lf,adjvs=%lf,tot=%lf,bot=%lf,dip=%lf,fault=%lf WHERE id=%ld;",
							psvy.adjtvd, psvy.adjvs, psvy.tot, psvy.bot, psvy.dip, psvy.fault, psvy.id);
						DoQuery(res_setout, query);
					}
					else
					{
						csvy.id = atol(FetchField(res_setin, "id"));
						csvy.depth = atof( FetchField(res_setin, "md") );
						csvy.tvd = atof( FetchField(res_setin, "tvd") );
						csvy.vs = atof( FetchField(res_setin, "vs") );
						csvy.dip=0.0;
						csvy.fault=0.0;
						// else if(i<26)	csvy.dip=radians(-4.8);
						csvy.adjtvd=csvy.tvd-fOffsetTVD;
						csvy.adjvs=csvy.vs-fOffsetVS;
						csvy.dip=radians(getSectionDip(psvy.adjvs, csvy.adjvs));
						// csvy.fault=getSectionFault(psvy.adjvs, csvy.adjvs);
						// printf("padjvs:%lf adjvs:%lf\n", psvy.adjvs, csvy.adjvs);
						if(keycheckOK) {
							csvy.tot=psvy.tot+(-tan(csvy.dip)*(csvy.vs-psvy.vs));
							csvy.bot=psvy.bot+(-tan(csvy.dip)*(csvy.vs-psvy.vs));
							csvy.tot+=csvy.fault;
							csvy.bot+=csvy.fault;
						}
						else {
							csvy.tot = -1;
							csvy.bot = -1;
							csvy.dip = -1;
							csvy.fault = -1;
						}
						// csvy.tot=getSectionTot(psvy.adjvs, csvy.adjvs);
						// csvy.bot=csvy.tot-(fStartBot-fStartTot);
						// printf("tot:%lf\n", csvy.tot);
						csvy.dip=degrees(csvy.dip);
						sprintf(query, "UPDATE addformsdata SET adjtvd=%lf,adjvs=%lf,tot=%lf,bot=%lf,dip=%lf,fault=%lf WHERE id=%ld;",
							csvy.adjtvd, csvy.adjvs, csvy.tot, csvy.bot, csvy.dip, csvy.fault, csvy.id);
						DoQuery(res_setout, query);
						memcpy(&psvy, &csvy, sizeof(T_SVY));
					}
					i++;
				}
				DoQuery(res_setout, "COMMIT");
			}
		}
	}

	FreeResult(res_setinfo);
	FreeResult(res_setin);
	FreeResult(res_setout);
	CloseDb();
	return 0;
}
