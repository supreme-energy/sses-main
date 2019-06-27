#include <math.h>
#include <tgmath.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <string.h>
#include <limits.h>

#include "dbio.h"
// #define USEKEYLOK 1
#ifdef USEKEYLOK
#include "../../vendor/keylok/linux/keylok.h"
#endif

float fStartTot=0.0;
float fStartBot = 0.0;
float fStartThickness = 0.0;
float fStartDip = 0.0;
float fStartFault = 0.0;
float fStartMD=0.0;
float fStartTVD=0.0;
float fStartVS=0.0;

ResultSet resultSetInfo;
ResultSet *res_setinfo = &resultSetInfo;
ResultSet resultSetIn;
ResultSet *res_setin = &resultSetIn;
ResultSet resultSetOut;
ResultSet *res_setout = &resultSetOut;

#define MAX_SVYS	1024

typedef struct t_addformsdata T_ADDFORMSDATA;
struct t_addformsdata {
	unsigned long id;
	float md, tvd, vs;
	float tot,bot;
	float thickness;
	float fault;
};
typedef struct t_afinfo T_AFINFO;
struct t_afinfo {
	int count;
	T_ADDFORMSDATA	data[MAX_SVYS];
};


// surveys
typedef struct t_svydata T_SVYDATA;
struct t_svydata {
	unsigned long id;
	float md,tvd,vs;
	float tot,bot;
	float fault;
};
typedef struct t_svyinfo T_SVYINFO;
struct t_svyinfo {
	int count;
	T_SVYDATA	data[MAX_SVYS];
};
T_SVYINFO svydata;

// projections
typedef struct t_projdata T_PROJDATA;
struct t_projdata {
	unsigned long id;
	float md,tvd,vs;
	float tot,bot;
	float fault;
};
typedef struct t_projinfo T_PROJINFO;
struct t_projinfo {
	int count;
	T_PROJDATA	data[MAX_SVYS];
};
T_PROJINFO projdata;

int verbose = 1;
char errorbuf[4095];
char query[4095];

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

void fetchSurveys(char *prog) {
	int i;
	sprintf(query, "SELECT * FROM surveys ORDER BY md;");
	if (DoQuery(res_set2, query)) {
		fprintf(stderr, "%s: Error in select query for table %s\n", prog, query);
		CloseDb();
		exit (-1);
	}
	svydata.count=0;
	while(FetchRow(res_set2)) {
		svydata.data[svydata.count].id=atol(FetchField(res_set2, "id"));
		svydata.data[svydata.count].md=atof(FetchField(res_set2, "md"));
		svydata.data[svydata.count].tvd=atof(FetchField(res_set2, "tvd"));
		svydata.data[svydata.count].vs=atof(FetchField(res_set2, "vs"));
		svydata.data[svydata.count].tot=atof(FetchField(res_set2, "tot"));
		svydata.data[svydata.count].fault=atof(FetchField(res_set2, "fault"));
		svydata.count++;
	}
	FreeResult(res_set2);

	sprintf(query, "SELECT * FROM projections ORDER BY md;");
	if (DoQuery(res_set2, query)) {
		fprintf(stderr, "%s: Error in select query for table %s\n", prog, query);
		CloseDb();
		exit (-1);
	}
	projdata.count=0;
	while(FetchRow(res_set2)) {
		projdata.data[projdata.count].id=atol(FetchField(res_set2, "id"));
		projdata.data[projdata.count].md=atof(FetchField(res_set2, "md"));
		projdata.data[projdata.count].tvd=atof(FetchField(res_set2, "tvd"));
		projdata.data[projdata.count].vs=atof(FetchField(res_set2, "vs"));
		projdata.data[projdata.count].tot=atof(FetchField(res_set2, "tot"));
		projdata.data[projdata.count].fault=atof(FetchField(res_set2, "fault"));
		projdata.count++;
	}
	FreeResult(res_set2);
}

/*****************************************************************************/

void doFormation(unsigned long infoid) {
	int i, j, numrows;
	int svyid, projid;
	T_AFINFO	afdata;
	float lastthickness=fStartThickness;
	float lastfault=0;
	float thick;
	float md, lastmd=0.0;

	sprintf(query, "SELECT * FROM addformsdata WHERE infoid=%ld ORDER BY md ASC;", infoid);
	if(DoQuery(res_setin, query)) {
		fprintf(stderr, "argv[0]: Error in select query");
		return;
	}
	afdata.count=0;
	while(FetchRow(res_setin)) {
		md = atof( FetchField(res_setin, "md") );
		if(md<=lastmd+.01)	continue;
		afdata.data[afdata.count].md = md;
		afdata.data[afdata.count].thickness=atof( FetchField(res_setin, "thickness") );
		afdata.count++;
		lastmd=md;
	}
	FreeResult(res_setin);
	
	DoQuery(res_setout, "BEGIN TRANSACTION;");
	sprintf(query, "DELETE FROM addformsdata WHERE infoid=%ld;", infoid);
	DoQuery(res_setout, query);
	for(i=0; i<svydata.count; i++) {
		thick=lastthickness;
		for(j=0; j<afdata.count; j++) {
			if(afdata.data[j].md>svydata.data[i].md) break;
			thick=afdata.data[j].thickness;
			lastmd=afdata.data[j].md;
			if(j==0) {
				sprintf(query, "UPDATE addforms SET thickness=%f WHERE id=%ld;", thick, infoid);
				DoQuery(res_setout, query);
			}
		}
		sprintf(query, "INSERT INTO addformsdata (infoid,svyid,md,tvd,vs,tot,fault,thickness) VALUES (%ld,%ld,%f,%f,%f,%f,%f,%f);",
			infoid,
			svydata.data[i].id,
			svydata.data[i].md,
			svydata.data[i].tvd,
			svydata.data[i].vs,
			svydata.data[i].tot + thick,
			svydata.data[i].fault,
			thick);
		DoQuery(res_setout, query);
		lastthickness=thick;
		lastfault=svydata.data[i].fault;
	}

	for(i=0; i<projdata.count; i++) {
		thick=lastthickness;
		for(j=0; j<afdata.count; j++) {
			if(afdata.data[j].md>projdata.data[i].md) break;
			thick=afdata.data[j].thickness;
			lastmd=afdata.data[j].md;
		}
		sprintf(query, "INSERT INTO addformsdata (infoid,projid,md,tvd,vs,tot,fault,thickness) VALUES (%ld,%ld,%f,%f,%f,%f,%f,%f);",
			infoid,
			projdata.data[i].id,
			projdata.data[i].md,
			projdata.data[i].tvd,
			projdata.data[i].vs,
			projdata.data[i].tot + thick -projdata.data[i].fault,
			lastfault,
			thick);
		DoQuery(res_setout, query);
		lastthickness=thick;
		lastfault=projdata.data[i].fault;
	}
	DoQuery(res_setout, "COMMIT");
	FreeResult(res_setout);
}

/*****************************************************************************/

void doAdditionalFormations(char *prog) {
	int i, infoid;

	if (DoQuery(res_setinfo, "SELECT * FROM addforms")) {
		fprintf(stderr, "argv[0]: Error in select query for info table");
		return;
	} else {
		while (FetchRow(res_setinfo)) {
			infoid = atol( FetchField(res_setinfo, "id") );
			fStartTot = atof( FetchField(res_setinfo, "tot") );
			fStartBot = atof( FetchField(res_setinfo, "bot") );
			fStartThickness = atof( FetchField(res_setinfo, "thickness") );
			doFormation(infoid);
		}
	}

	FreeResult(res_setinfo);
}


/*****************************************************************************/

void barfAndDie(char* prog) {
	printf("Usage: %s -d <dbname>\n", prog);
	exit(1);
}

/*****************************************************************************/

int main (int argc, char *argv[])
{
	int i;
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

	fetchSurveys(argv[0]);
	doAdditionalFormations(argv[0]);
	CloseDb();
	return 0;
}
