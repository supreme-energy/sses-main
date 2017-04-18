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

float fStartmd=0.0;
int bDoWellplan = 0;
int bDoImportWellplan = 0;
int bDoProjections=0;
char tablename[256];
char pterm_method[2];
char importfilename[4065];
char dbname[4065];
float bitoffset = 0.0;
float projection = 0.0;
float projdip = 0.0;

int pamethod=0;
int pbmethod=0;
int sgta_off=0;
float padata[4];
float pbdata[4];
unsigned long fsvy_id;
double fsvy_vs;

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
	float tpos, bpos;
	float fault;
	int method;
	char data[1024];
	char ptype[1024];
	char is_new[1];
};

int verbose = 1;
char errorbuf[4095];

float softwareVersion = 0.0;
int	keycheckOK = 0;
unsigned char softwareOptions = 0;
unsigned int keySerialNumber = 0;
double deltacl;
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

void projectInc(T_SVY *pSvy, T_SVY *cSvy, float *data, double pa)
{
	float dtvd, cd, ca, pinc, ew, ns, vs, dns, dew, disp, md, inc, azm, dl, tvd;
	dtvd=data[0];
	tvd=pSvy->tvd+dtvd;
	cd=pSvy->cd+data[1];
	ca=radians(pSvy->ca)+radians(data[2]);
	pinc=radians(pSvy->inc);
	ew=sin(ca)*cd;
	ns=cos(ca)*cd;
	vs=cd*cos(ca-pa);
	dns=ns-pSvy->ns;
	dew=ew-pSvy->ew;
	if(dns!=0.0) azm=atan2(dew, dns);
	else if(dew<0.0) azm=radians(270.0);
	else azm=radians(90.0);
	disp=sqrt((dns*dns)+(dew*dew));
	md=pSvy->depth+sqrt((dtvd*dtvd)+(disp*disp));
	inc=(2.0*atan(disp/dtvd))-pinc;
	dl=acos(
		(cos(pinc) * cos(inc)) +
		(sin(pinc) * sin(inc) * cos(azm-pinc))
		);

	cSvy->depth=md;
	cSvy->inc=degrees(inc);
	if(cSvy->inc<0.0)	cSvy->inc+=360.0;
	cSvy->azm=degrees(azm);
	if(cSvy->azm<0.0)	cSvy->azm+=360.0;
	cSvy->vs=vs;
	cSvy->ns=ns;
	cSvy->ew=ew;
	cSvy->dl=dl;
	cSvy->tvd=tvd;
	cSvy->cd=cd;
	cSvy->ca=degrees(ca);
}

/*****************************************************************************/

void projectAzm(T_SVY *pSvy, T_SVY *cSvy, float *data, double pa)
{
	float tvd, cd, ca, dtvd, ew, ns, vs, dns, dew, disp, md, pinc, inc, azm, dl;
	dtvd=data[0];
	tvd=pSvy->tvd+dtvd;
	cd=pSvy->cd+data[1];
	ca=radians(pSvy->ca)+radians(data[2]);
	ew=sin(ca)*cd;
	ns=cos(ca)*cd;
	vs=cd*cos(ca-pa);
	dns=ns-pSvy->ns;
	dew=ew-pSvy->ew;
	disp=sqrt((dns*dns)+(dew*dew));
	md=pSvy->depth+sqrt((dtvd*dtvd)+(disp*disp));
	pinc=radians(pSvy->inc);

	if(dtvd!=0.0) inc=(2.0*atan(disp/dtvd))-pinc;
	else inc=radians(90.0);

	if(dns!=0.0) azm=degrees(atan(dew/ dns));
	else if(dew<0.0) azm=270.0;
	else azm=90.0;
	if(cSvy->azm<0.0)	cSvy->azm+=360.0;
	azm=(2.0*azm)-pSvy->azm;
	if(cSvy->azm<0.0)	cSvy->azm+=360.0;

	dl=acos(
		(cos(pinc) * cos(inc)) +
		(sin(pinc) * sin(inc) * cos(azm-pinc))
		);

	cSvy->depth=md;
	cSvy->inc=degrees(inc);
	if(cSvy->inc<0.0)	cSvy->inc+=360.0;
	cSvy->azm=azm;
	cSvy->vs=vs;
	cSvy->ns=ns;
	cSvy->ew=ew;
	cSvy->dl=dl;
	cSvy->tvd=tvd;
	cSvy->cd=cd;
	cSvy->ca=degrees(ca);
}

/*****************************************************************************/

void calccurv(T_SVY *pSvy, T_SVY *cSvy, double propazm, int depthunits)
{
	double courselength = cSvy->depth - pSvy->depth;
	double pInc, cInc, pAzm, cAzm;
	double dogleg, doglegseverity = 0.0;
	double radius = 1.0;

	pInc = radians(pSvy->inc);
	cInc = radians(cSvy->inc);
	pAzm = radians(pSvy->azm);
	cAzm = radians(cSvy->azm);

	dogleg = acos((cos(pInc) * cos(cInc)) + (sin(pInc) * sin(cInc) * cos(cAzm - pAzm)));
	if(isnan(dogleg)){
		cSvy->azm+=0.01;
		cAzm =  radians(cSvy->azm);
		dogleg = acos((cos(pInc) * cos(cInc)) + (sin(pInc) * sin(cInc) * cos(cAzm - pAzm)));
	}
	if (courselength > 0)
	{
		if(depthunits==1)
			doglegseverity = (dogleg * 30.0) / courselength;
		else
			doglegseverity = (dogleg * 100.0) / courselength;
	}

	if (dogleg != 0.0) radius = (2.0 / dogleg) * tan(dogleg / 2.0);
	else radius=1.0;

	cSvy->tvd = pSvy->tvd + ((courselength / 2.0) * (cos(pInc) + cos(cInc)) * radius);
	printf("dogleg: %f cinc:%f radius:\n",dogleg,cInc,radius);
	printf("tvd:%f\n",cSvy->tvd);
	cSvy->ns = pSvy->ns + ((courselength / 2.0) * ((sin(pInc) * cos(pAzm)) + (sin(cInc) * cos(cAzm))) * radius);
	cSvy->ew = pSvy->ew + ((courselength / 2.0) * ((sin(pInc) * sin(pAzm)) + (sin(cInc) * sin(cAzm))) * radius);

	if (cSvy->ns != 0.0) cSvy->ca = atan2(cSvy->ew, cSvy->ns);
	else cSvy->ca = M_PI_2;

	if (cSvy->ca != 0.0)
		cSvy->cd = (cSvy->ew / sin(cSvy->ca));
	else
		cSvy->cd = cSvy->ns;

	cSvy->vs = cos(cSvy->ca - propazm) * cSvy->cd;
	// printf("\tMD:%f VS:%f\n", pSvy->depth, pSvy->vs);

	cSvy->dl = degrees(doglegseverity);
	cSvy->ca = degrees(cSvy->ca);
	if(cSvy->ca < 0.0) cSvy->ca+=360.0;

	cSvy->build = ((cSvy->inc - pSvy->inc) * 100) / courselength;
	cSvy->turn = ((cSvy->azm - pSvy->azm) * 100) / courselength;

	cSvy->cl = courselength;
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


void barfAndDie(char* prog) {
	printf("Usage: %s -d <dbname>\n\
	-s (start md)\n\
	-w (calculate wellplan surveys)\n\
	-p (calculate projections)\n\
	-i (import and calculate wellplan surveys)\n\
", prog);
	exit(1);
}

/*****************************************************************************/

int main (int argc, char *argv[])
{
	int i, j;
	T_SVY psvy, csvy;
	T_SVY t_csvy, t_psvy;
	double dazm, dinc, ddepth;
	double deltamd;
	char query[4095];
	double propazm = 0.0;
	int depthunits=0;
	int is_new;
	double svyint=30.0;
	double dogleg = 0.0;
	float nfault=0;
	float ndip=0;
	char *tok=NULL;
	float fnum;

	bDoWellplan=0;
	bDoImportWellplan=0;
	strcpy(tablename, "surveys");
	strcpy(importfilename, "\0");
	strcpy(dbname, "\0");
	for (i = 1; i < argc; i++)
    {
		if (!strcmp (argv[i], "-t")) {
			strcpy( tablename, argv[++i] );
		}
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
	if (DoQuery(res_set, "SELECT * FROM appinfo LIMIT 1 OFFSET 0"))
	{
		fprintf(stderr, "argv[0]: Error in select query for info table");
		return -1;
	}
	else
	{
		FetchRow(res_set);
		sgta_off = atoi(FetchField(res_set,"sgta_off"));

	}
	FreeResult(res_set);

	// fetch propsed azimuth from info table
	sprintf(query,"SELECT * from anticollision_wells where tablename='%s' LIMIT 1 OFFSET 0",tablename);
	if (DoQuery(res_set,query))
	{
		fprintf(stderr, "argv[0]: Error in select query for info table");
		return -1;
	}
	else
	{
		FetchRow(res_set);
		propazm = radians( atof( FetchField(res_set, "propdir") ) );
	}

	FreeResult(res_set);
	sprintf(query, "SELECT * FROM %s ORDER BY md ASC;", tablename);
	if(DoQuery(res_set, query)) {
		fprintf(stderr, "argv[0]: Error in select query");
		return -1;
	}


	i=0;
	DoQuery(res_set2, "BEGIN TRANSACTION;");
	char *newtrue = "t";
	while(FetchRow(res_set)) {

		if(i==0) {
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

			if (psvy.ns != 0) psvy.ca = atan2(psvy.ew, psvy.ns);
			else psvy.ca = M_PI_2;
			if (psvy.ca != 0.0) psvy.cd = fabs(psvy.ew / sin(psvy.ca));
			else psvy.cd = psvy.ns;
			psvy.ca = degrees(psvy.ca);
			if(psvy.ca < 0.0) psvy.ca+=360.0;
			if(csvy.depth>=fStartmd) {
				// printf("Update tie-in\n");
				sprintf(query, "UPDATE %s set ca=%lf,cd=%lf WHERE id=%ld;", tablename, psvy.ca, psvy.cd, psvy.id);
				DoQuery(res_set2, query);
			}
		} else {
			csvy.id = atol(FetchField(res_set, "id"));
			csvy.depth = atof( FetchField(res_set, "md") );
			csvy.inc = atof( FetchField(res_set, "inc") );
			csvy.azm = atof( FetchField(res_set, "azm") );
			csvy.tvd = atof( FetchField(res_set, "tvd") );
			csvy.ns = atof( FetchField(res_set, "ns") );
			csvy.ew = atof( FetchField(res_set, "ew") );
			csvy.vs = atof( FetchField(res_set, "vs") );
			csvy.ca = atof( FetchField(res_set, "ca") );
			csvy.cd = atof( FetchField(res_set, "cd") );
			csvy.dl = atof( FetchField(res_set, "dl") );

			if(keycheckOK) {
				svyint=50.0;
				ddepth = csvy.depth - psvy.depth;
				calccurv(&psvy, &csvy, propazm, depthunits);
			}
			else {
				csvy.tvd = -1;
				csvy.vs = -1;
				csvy.ns = -1;
				csvy.ew = -1;
				csvy.ca = -1;
				csvy.cd = -1;
				csvy.dl = -1;
				csvy.cl = -1;
			}
			if(csvy.depth>=fStartmd) {
				// printf("Update survey. ");
				sprintf(query, "UPDATE %s SET tvd=%lf,vs=%lf,ns=%lf,ew=%lf,ca=%lf,cd=%lf,dl=%lf,cl=%lf WHERE id=%ld;",
					tablename, csvy.tvd, csvy.vs, csvy.ns, csvy.ew, csvy.ca,
					csvy.cd, csvy.dl, csvy.cl, csvy.id);
				DoQuery(res_set2, query);
			} // end of: if(!bDoProjections)

			// pre-calculate the deltas for dogleg projections
			memcpy(&psvy, &csvy, sizeof(T_SVY));
		}
		i++;
	}
	if(DoQuery(res_set2, "COMMIT;")){
		fprintf(stderr, "%s: Error in commit query\n", argv[0]);
	}
	FreeResult(res_set2);
	FreeResult(res_set);

	CloseDb();
	return 0;
}
