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

void projtva(T_SVY *pSvy, T_SVY *cSvy, double propazm, int depthunits) {
	float inc, ns, ew, ca, cd, dl, cl;
	float newinc, newvs, newdepth, newtvd, radius;
	float dtvd = cSvy->tvd - pSvy->tvd;
	float dvs= cSvy->vs - pSvy->vs;
	float r=sqrt((dtvd*dtvd)+(dvs*dvs));
	float pinc=radians(pSvy->inc);
	float pazm=radians(pSvy->azm);
	float azm=radians(cSvy->azm);
	float tvd=cSvy->tvd + cSvy->fault;
	float vs=cSvy->vs;
	float incr=.01;
	unsigned long counter=0;
	newdepth=pSvy->depth+r;
	newinc=0.0;
	if(dtvd!=0.0 || dvs!=0.0) {
		do {
			cl=newdepth-pSvy->depth;
			if(newinc>=degrees(pinc)-incr && newinc<=degrees(pinc)+incr) {
				if(newvs<vs && newtvd>tvd) { newdepth+=incr; newinc+=incr; }
				else if(newvs>vs && newtvd<=tvd) { newinc-=incr; }
				else if(newvs>vs && newtvd<tvd) { newinc-=incr; newdepth-=incr; }
				continue;
			}
			inc=radians(newinc);
			dl=acos( (cos(pinc) * cos(inc)) + (sin(pinc) * sin(inc) * cos(azm-pazm)));
			if (dl!=0.0) radius=(2.0/dl) * tan(dl/2.0); else radius=1.0;
			newtvd=pSvy->tvd+((cl/2.0)*(cos(pinc)+cos(inc))*radius);
			ns=pSvy->ns +( (cl/2.0)* ((sin(pinc) * cos(pazm)) + (sin(inc) * cos(azm))) * radius);
			ew=pSvy->ew + ( (cl/2.0)* ((sin(pinc) * sin(pazm)) + (sin(inc) * sin(azm))) * radius);
			if (ns!=0) ca=atan2(ew,ns); else ca=(M_PI * .5);
			if (ca!=0.0) cd=fabs(ew/sin(ca)); else cd=ns;
			newvs=cd * cos(ca-propazm);
			if(fabs(newvs-vs)<2.0 && fabs(newtvd-tvd)<2.0)	incr=.001;
			if(newvs<vs && newtvd>tvd) { newdepth+=incr; newinc+=incr; }
			else if(newvs<vs && newtvd<=tvd) { newdepth+=incr; }
			else if(newvs>vs && newtvd<=tvd) { newinc-=incr; }
			else if(newvs>=vs && newtvd>tvd) { newdepth-=incr; }
			else if(newvs>vs && newtvd<tvd) { newinc-=incr; newdepth-=incr; }
		} while ( (newtvd>tvd+incr || newvs<vs-incr) && newinc>0 && newinc<=180 && ++counter<32000 );
		// } while ( (newtvd>tvd+incr || newvs<vs-incr) && newinc>0 && newinc<=180 && ++counter<(3200000) );
		ca=degrees(ca); if(ca<0.0)	ca=360.0+ca;
		dl=degrees((dl*100)/cl);
		newinc=degrees(pinc+((inc-pinc)/2.0));
		cSvy->depth=newdepth;
		cSvy->inc=newinc;
		cSvy->ns=ns;
		cSvy->ew=ew;
		cSvy->cd=cd;
		cSvy->ca=ca;
		cSvy->dl=dl;
		// printf("pdepth:%.2f, ptvd:%.2f, pvs:%.2f\n", pSvy->depth, pSvy->tvd, pSvy->vs);
	}
}

/*****************************************************************************/

double calcdl(T_SVY *pSvy, T_SVY *cSvy, double propazm, int depthunits)
{
	double courselength = cSvy->depth - pSvy->depth;
	double pInc, cInc, pAzm, cAzm;
	double dogleg, doglegseverity = 0.0;
	pInc = radians(pSvy->inc);
	cInc = radians(cSvy->inc);
	pAzm = radians(pSvy->azm);
	cAzm = radians(cSvy->azm);
	dogleg = acos((cos(pInc) * cos(cInc)) + (sin(pInc) * sin(cInc) * cos(cAzm - pAzm)));
	if (courselength > 0)
	{
		if(depthunits==1)
			doglegseverity = (dogleg * 30.0) / courselength;
		else
			doglegseverity = (dogleg * 100.0) / courselength;
	}
	return (degrees(doglegseverity));
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

void importWellplan(double propazm) {
	FILE *inputfile;
	char *tok;
	int i;
	char line[1024];
	char query[1024];
	double ca, cd, vs;
	T_SVY svy;
	svy.vs = svy.ns = svy.ew = svy.tvd = svy.ca = svy.cd = 0.0;

	inputfile = fopen(importfilename, "rt");
	if(inputfile != NULL)
	{
		DoQuery(res_set2, "DELETE FROM wellplan;");
		FreeResult(res_set2);
		DoQuery(res_set2, "BEGIN TRANSACTION;");
		while ( fgets(line, 1024, inputfile) != NULL) {
			strcpy(line, trimwhitespace(line));
			if(isdigit(line[0]) || line[0]=='.' || line[0]=='-') {
				tok = strtok(line, " ,\t");
				if(tok==NULL) {
					DoQuery(res_set2, "COMMIT");
					FreeResult(res_set2);
					return;
				}
				svy.depth = atof(tok);

				tok = strtok(NULL, " ,\t\n\r");
				if(tok==NULL) {
					DoQuery(res_set2, "COMMIT");
					FreeResult(res_set2);
					return;
				}
				svy.inc = atof(tok);

				tok = strtok(NULL, " ,\t\n\r");
				if(tok==NULL) {
					DoQuery(res_set2, "COMMIT");
					FreeResult(res_set2);
					return;
				}
				svy.azm = atof(tok);
/*
				tok = strtok(NULL, " \t\n\r");
				if(tok==NULL) {
					DoQuery(res_set2, "COMMIT");
					FreeResult(res_set2);
					return;
				}
				svy.tvd = atof(tok);

				tok = strtok(NULL, " \t\n\r");
				if(tok==NULL) {
					DoQuery(res_set2, "COMMIT");
					FreeResult(res_set2);
					return;
				}
				svy.vs = atof(tok);

				tok = strtok(NULL, " \t\n\r");
				if(tok==NULL) {
					DoQuery(res_set2, "COMMIT");
					FreeResult(res_set2);
					return;
				}
				svy.ns = atof(tok);

				tok = strtok(NULL, " \t\n\r");
				if(tok==NULL) {
					DoQuery(res_set2, "COMMIT");
					FreeResult(res_set2);
					return;
				}
				svy.ew = atof(tok);
*/
				if (svy.ns != 0) svy.ca = atan2(svy.ew, svy.ns);
				else svy.ca = M_PI_2;
				if (svy.ca != 0.0) svy.cd = fabs(svy.ew / sin(svy.ca));
				else svy.cd = svy.ns;
				svy.vs = svy.cd * cos(svy.ca - propazm);
				svy.dl=0.0;
				svy.ca = degrees(svy.ca);
				if(svy.ca < 0.0) svy.ca+=360.0;
				sprintf(query,
					"INSERT INTO wellplan (md, inc, azm, tvd, vs, ns, ew, ca, cd, dl, hide) VALUES \
					(%lf, %lf, %lf, %lf, %lf, %lf, %lf, %lf, %lf, %lf, %d);",
					svy.depth, svy.inc, svy.azm, svy.tvd, svy.vs, svy.ns, svy.ew, svy.ca, svy.cd, svy.dl, 0);
				DoQuery(res_set2, query);
			}
		}
		DoQuery(res_set2, "COMMIT;");
		FreeResult(res_set2);
		fclose(inputfile);
	}
}

/*****************************************************************************/

float getSectionFault(float sd, float ed) {
	char q[256];
	int i;
	float n=0;

	sprintf(q,
	"SELECT * FROM welllogs WHERE startmd>=%f AND endmd<=%f;", sd-.01, ed+.01);
	// "SELECT * FROM welllogs ORDER BY abs(%f-endmd) LIMIT 1;", ed);

	if(DoQuery(res_set3, q))
		return 0.0;

	for(i=0; FetchRow(res_set3); i++) {
		n+=atof(FetchField(res_set3, "fault"));
	}
	FreeResult(res_set3);
	return n;
}

/*****************************************************************************/

float getSectionDip(float sd, float ed) {
	char q[256];
	int i;
	float n=0;
	float dip, depth;

	sprintf(q,
	"SELECT * FROM welllogs WHERE startmd>=%f AND endmd<=%f;",
	sd-.01, ed+.01);

	if(DoQuery(res_set3, q))
		return 0.0;

	// printf("getSectionDip: sd:%.2f ed:%.2f\n", sd, ed);

	for(i=0; FetchRow(res_set3); i++) {
		dip=atof(FetchField(res_set3, "dip"));
		// depth=atof(FetchField(res_set3, "startmd"));
		// printf("Found %.2f dip at %.2f feet\n", dip, depth);
		n+=dip;
	}
	FreeResult(res_set3);
	if(i<=0)	return 0.0;
	n/=(float)i;
	// printf("Total dip:%.2f\n", n);
	return n;
}

/*****************************************************************************/

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
		if (!strcmp (argv[i], "-s")) {
			fStartmd=atof(argv[++i]);
		}
		if (!strcmp (argv[i], "-w")) {
			bDoWellplan = 1;
			strcpy(tablename, "wellplan");
		}
		if (!strcmp (argv[i], "-i")) {
			bDoWellplan = 1;
			bDoImportWellplan = 1;
			strcpy(tablename, "wellplan");
			i++;
			strcpy(importfilename, argv[i]);
		}
		if (!strcmp (argv[i], "-p")) bDoProjections = 1;
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
	if (DoQuery(res_set, "SELECT * FROM wellinfo LIMIT 1 OFFSET 0"))
	{
		fprintf(stderr, "argv[0]: Error in select query for info table");
		return -1;
	}
	else
	{
		FetchRow(res_set);
		propazm = radians( atof( FetchField(res_set, "propazm") ) );
		depthunits = atoi( FetchField(res_set, "depthunits") );
		bitoffset = atof( FetchField(res_set, "bitoffset") );
		projection = atof( FetchField(res_set, "projection") );
		projdip = atof( FetchField(res_set, "projdip") );
		strcpy(pterm_method,FetchField(res_set,"pterm_method"));
		pamethod = atoi( FetchField(res_set, "pamethod") );
		pbmethod = atoi( FetchField(res_set, "pbmethod") );
		strcpy(query, FetchField(res_set, "padata"));
		padata[0]=padata[1]=padata[2]=padata[3]=0.0;
		if(strlen(query)>0) {
			tok=strtok(query, ",");
			if(tok!=NULL) {
				for(i=0; i<4 && tok!=NULL; i++) {
					padata[i]=atof(tok);
					tok=strtok(NULL, ",");
				}
			}
		}
		strcpy(query, FetchField(res_set, "pbdata"));
		pbdata[0]=pbdata[1]=pbdata[2]=pbdata[3]=0.0;
		if(strlen(query)>0) {
			tok=strtok(query, ",");
			if(tok!=NULL) {
				for(i=0; i<4 && tok!=NULL; i++) {
					pbdata[i]=atof(tok);
					tok=strtok(NULL, ",");
				}
			}
		}
	}

	FreeResult(res_set);

	if(bDoWellplan != 0) {
		DoQuery(res_set2, "DELETE FROM wellplan WHERE NOT hide='0'");
		FreeResult(res_set2);
	}
	if(bDoImportWellplan != 0) {
		importWellplan(propazm);
	}

	// cycle through surveys
	DoQuery(res_set2, "DELETE FROM surveys WHERE plan!=0;");
	if(bDoWellplan != 0) {
		sprintf(query, "SELECT * FROM %s WHERE plan=0 ORDER BY md ASC;", tablename);
	} else {
		sprintf(query, "SELECT * FROM %s ORDER BY md ASC;", tablename);
		/* from gva:
		sprintf(query, "SELECT count(id) FROM welllogs WHERE startmd>=%f;", modelStartAt);
		if (DoQuery(res_set, query)) {
			fprintf(stderr, "%s: Error in select query for welllogs\n", argv[0]);
			CloseDb();
			exit (-1);
		}
		else if(FetchRow(res_set)) {
				i=atoi( FetchField(res_set, "count") );
				sprintf(query, "SELECT * FROM welllogs ORDER BY startmd ASC LIMIT %d;", i+2);
				// printf("%s\n", query);
		}
		else sprintf(query, "SELECT * FROM %s ORDER BY md ASC;", tablename);
		*/
		// sprintf(query, "SELECT * FROM %s WHERE md>=%f ORDER BY md ASC;", tablename, fStartmd);
		// printf("\nquery: %s\n", query);
	}
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
			if(!bDoWellplan) {
				strcpy(psvy.is_new ,FetchField(res_set,"new"));
				psvy.tot = atof( FetchField(res_set, "tot") );
				psvy.bot = atof( FetchField(res_set, "bot") );
				psvy.dip = atof( FetchField(res_set, "dip") );
				psvy.fault = atof( FetchField(res_set, "fault") );
			}
			if (psvy.ns != 0) psvy.ca = atan2(psvy.ew, psvy.ns);
			else psvy.ca = M_PI_2;
			if (psvy.ca != 0.0) psvy.cd = fabs(psvy.ew / sin(psvy.ca));
			else psvy.cd = psvy.ns;
			psvy.ca = degrees(psvy.ca);
			if(psvy.ca < 0.0) psvy.ca+=360.0;
			if(!bDoProjections && csvy.depth>=fStartmd) {
				// printf("Update tie-in\n");
				sprintf(query, "UPDATE %s set ca=%lf,cd=%lf WHERE id=%ld;", tablename, psvy.ca, psvy.cd, psvy.id);
				DoQuery(res_set2, query);
			}
			if(bDoProjections && strcmp(newtrue,psvy.is_new)==0){
				sprintf(query,"UPDATE %s set \"new\"=false where id=%ld",tablename,psvy.id);
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

			if(!bDoWellplan) {
				strcpy(csvy.is_new ,FetchField(res_set,"new"));
				csvy.tot = atof( FetchField(res_set, "tot") );
				csvy.bot = atof( FetchField(res_set, "bot") );
				csvy.dip = atof( FetchField(res_set, "dip") );
				csvy.fault = atof( FetchField(res_set, "fault") );
			}
			if(!sgta_off){
				csvy.dip=getSectionDip(psvy.depth, csvy.depth);
				csvy.fault=getSectionFault(psvy.depth, csvy.depth);
			} else{
				if(bDoWellplan){
					csvy.dip=0;
					csvy.fault=0;
				}else{
					csvy.dip = atof( FetchField(res_set, "dip") );
					csvy.fault = atof( FetchField(res_set, "fault") );
				}


			}
			nfault+=csvy.fault;

			if(keycheckOK) {
				svyint=50.0;
				ddepth = csvy.depth - psvy.depth;
				dogleg = calcdl(&psvy, &csvy, propazm, depthunits);
				// printf("MD:%f Inc:%f Azm:%f DL:%f\n", csvy.depth, csvy.inc, csvy.azm, dogleg);
				if(bDoWellplan!=0 && dogleg != 0 && ddepth > 0) {
					//fprintf(stderr,"10%i\n",i);
					dazm=(csvy.azm-psvy.azm)/ddepth;
					dinc=(csvy.inc-psvy.inc)/ddepth;
					memcpy(&t_csvy, &csvy, sizeof(T_SVY));
					memcpy(&t_psvy, &psvy, sizeof(T_SVY));
					t_csvy.dl=dogleg;
					t_csvy.inc = t_psvy.inc;
					if(dazm!=0.0 || dinc!=0.0) {
						fprintf(stderr,"11%i\n",i);
						t_csvy.azm=t_psvy.azm;
						t_csvy.inc=t_psvy.inc;
						for(ddepth = t_psvy.depth + svyint; ddepth < csvy.depth - svyint; ddepth += svyint) {
							//fprintf(stderr,"12%i\n",i);
							t_csvy.depth = ddepth;
							t_csvy.azm+=(dazm*svyint);
							t_csvy.inc+=(dinc*svyint);
							calccurv(&t_psvy, &t_csvy, propazm, depthunits);
							// printf("\tMD:%f Inc:%f Azm:%f DL:%f\n", t_csvy.depth, t_csvy.inc, t_csvy.azm, t_csvy.dl);
							sprintf(query,
								"INSERT INTO wellplan (md,inc,azm,tvd,vs,ns,ew,ca,cd,dl,hide) VALUES \
								(%lf,%lf,%lf,%lf,%lf,%lf,%lf,%lf,%lf,%lf,%d);",
								t_csvy.depth, t_csvy.inc, t_csvy.azm,
								t_csvy.tvd, t_csvy.vs, t_csvy.ns, t_csvy.ew,
								t_csvy.ca, t_csvy.cd, t_csvy.dl, 1);
							//fprintf(stderr,"10%i:%s\n",i,query);
							if(DoQuery(res_set3, query)==0)
								FreeResult(res_set3);
							memcpy(&t_psvy, &t_csvy, sizeof(T_SVY));
						}
					}
				}

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
			if(!bDoProjections && csvy.depth>=fStartmd) {
				// printf("Update survey. ");
				sprintf(query, "UPDATE %s SET tvd=%lf,vs=%lf,ns=%lf,ew=%lf,ca=%lf,cd=%lf,dl=%lf,cl=%lf WHERE id=%ld;",
					tablename, csvy.tvd, csvy.vs, csvy.ns, csvy.ew, csvy.ca,
					csvy.cd, csvy.dl, csvy.cl, csvy.id);
				DoQuery(res_set2, query);
			} // end of: if(!bDoProjections)
			if(bDoProjections && strcmp(newtrue,csvy.is_new)==0){
				printf("csvy is new %s %ld\n",
										csvy.is_new,
										csvy.id
										);
				sprintf(query,"UPDATE %s set \"new\"=false where id=%ld",tablename,csvy.id);
				DoQuery(res_set2,query);
			}
			// pre-calculate the deltas for dogleg projections
			deltamd=csvy.depth-psvy.depth;
			if(strcmp(newtrue,csvy.is_new)==0){
				deltacl = csvy.cl;
			}else{
				deltacl= 0.0;
			}
			if(deltamd!=0.0) {
				dinc=(csvy.inc-psvy.inc)/deltamd;
				dazm=(csvy.azm-psvy.azm)/deltamd;
			} else dinc=dazm=0.0;
			memcpy(&psvy, &csvy, sizeof(T_SVY));
		}
		i++;
	}

	// now plug in the bitoffset and projection surveys
	if(bDoProjections) {
	 printf("Do Projections\n");
		if(i>1) {
			csvy.dip=projdip;
			// project to bit
			if(bitoffset>=1 && pbmethod==0) {	// last dogleg
				csvy.depth+=bitoffset;
				ddepth=csvy.depth-psvy.depth;
				csvy.inc+=dinc*ddepth;
				csvy.azm+=dazm*ddepth;
				calccurv(&psvy, &csvy, propazm, depthunits);
				sprintf(query, "INSERT INTO surveys (md,inc,azm,plan) VALUES (%lf,%lf,%lf,%d);",
					csvy.depth, csvy.inc, csvy.azm, 1);
				printf("query:%s\n", query);
				DoQuery(res_set2, query);
				sprintf(query, "UPDATE %s SET tvd=%lf,vs=%lf,ns=%lf,ew=%lf,ca=%lf,cd=%lf,dl=%lf,cl=%lf WHERE md=%lf;",
					tablename, csvy.tvd, csvy.vs, csvy.ns, csvy.ew, csvy.ca,
					csvy.cd, csvy.dl, csvy.cl, csvy.depth);
				// printf("query:%s\n", query);
				DoQuery(res_set2, query);
				// find our proposed new tot/bot
				/* done in sses_gva */
				csvy.tot=psvy.tot+(-tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs));
				csvy.bot=psvy.bot+(-tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs));
				// csvy.tot+=csvy.fault; csvy.bot+=csvy.fault;
				csvy.tpos=csvy.tot-csvy.tvd;
				// sprintf(query, "UPDATE projections SET tot=%f,bot=%f WHERE id=%ld;", csvy.tot, csvy.bot, csvy.id);
				sprintf(query, "UPDATE surveys SET tot=%f,bot=%f,dip=%f WHERE md=%lf;",
					csvy.tot, csvy.bot, csvy.dip, csvy.depth);
				// printf("query:%s\n", query);
				DoQuery(res_set2, query);
				// pre-calculate the deltas for dogleg projections
				deltamd=csvy.depth-psvy.depth;
				if(deltamd!=0.0) {
					dinc=(csvy.inc-psvy.inc)/deltamd;
					dazm=(csvy.azm-psvy.azm)/deltamd;
				} else dinc=dazm=0.0;
				memcpy(&psvy, &csvy, sizeof(T_SVY));
			}
			else if(pbmethod>=3 && pbmethod<=5) {	// ROC
				csvy.depth=psvy.depth+pbdata[0];
				csvy.inc=psvy.inc+pbdata[1];
				csvy.azm=psvy.azm+pbdata[2];
				if(csvy.azm<0)	csvy.azm+=360.0;
				if(csvy.azm>360.0)	csvy.azm-=360.0;
				calccurv(&psvy, &csvy, propazm, depthunits);
				sprintf(query, "INSERT INTO surveys (md,inc,azm,plan) VALUES (%lf,%lf,%lf,%d);",
					csvy.depth, csvy.inc, csvy.azm, 1);
				printf("query:%s\n", query);
				if(DoQuery(res_set2, query)){
					fprintf(stderr, "argv[0]: Error in insert select query");
				}
				sprintf(query, "UPDATE %s SET tvd=%lf,vs=%lf,ns=%lf,ew=%lf,ca=%lf,cd=%lf,dl=%lf,cl=%lf WHERE md=%lf;",
					tablename, csvy.tvd, csvy.vs, csvy.ns, csvy.ew, csvy.ca,
					csvy.cd, csvy.dl, csvy.cl, csvy.depth);
				// printf("query:%s\n", query);
				DoQuery(res_set2, query);
				// find our proposed new tot/bot
				/* done in sses_gva */
				csvy.tot=psvy.tot+(-tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs));
				csvy.bot=psvy.bot+(-tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs));
				// csvy.tot+=csvy.fault; csvy.bot+=csvy.fault;
				csvy.tpos=csvy.tot-csvy.tvd;
				// sprintf(query, "UPDATE projections SET tot=%f,bot=%f WHERE id=%ld;", csvy.tot, csvy.bot, csvy.id);
				sprintf(query, "UPDATE surveys SET tot=%f,bot=%f,dip=%f WHERE md=%lf;", csvy.tot, csvy.bot, csvy.dip, csvy.depth);
				// printf("query:%s\n", query);
				DoQuery(res_set2, query);
				// pre-calculate the deltas for dogleg projections
				deltamd=csvy.depth-psvy.depth;
				if(deltamd!=0.0) {
					dinc=(csvy.inc-psvy.inc)/deltamd;
					dazm=(csvy.azm-psvy.azm)/deltamd;
				} else dinc=dazm=0.0;
				memcpy(&psvy, &csvy, sizeof(T_SVY));
			}
		}
		if(DoQuery(res_set2, "COMMIT;")){
			fprintf(stderr, "%s: Error in commit query\n", argv[0]);
		}

		// now for projections table
		FreeResult(res_set);
		char *cmprto = "bc";
		// check to see mode, if bp load final projection
		if(strcmp(pterm_method,cmprto)!=0){

			sprintf(query,"SELECT * FROM projections ORDER BY md DESC limit 1;");
			DoQuery(res_set,query);
			FetchRow(res_set);
			fsvy_id = atol(FetchField(res_set,"id"));

			fsvy_vs = atof(FetchField(res_set,"vs"));
			printf("compare projection vs is %lf",fsvy_vs);

		}
		FreeResult(res_set);
		if(bDoWellplan==0) sprintf(query, "SELECT * FROM projections ORDER BY md ASC;");
		if(DoQuery(res_set, query)) {
			fprintf(stderr, "%s: Error in select query\n", argv[0]);
			return -1;
		} else {
			while(FetchRow(res_set)) {
				DoQuery(res_set2, "BEGIN TRANSACTION;");
				csvy.id = atol(FetchField(res_set, "id"));
				strcpy(csvy.data, FetchField(res_set, "data"));
				csvy.method = atoi( FetchField(res_set, "method") );
				strcpy(csvy.ptype,FetchField(res_set,"ptype"));
				if(csvy.ptype=="rot"){
					csvy.method=3;
				}
				csvy.depth = atof( FetchField(res_set, "md") );
				csvy.inc = atof( FetchField(res_set, "inc") );
				csvy.azm = atof( FetchField(res_set, "azm") );
				csvy.ns = atof( FetchField(res_set, "ns") );
				csvy.ew = atof( FetchField(res_set, "ew") );
				csvy.tvd = atof( FetchField(res_set, "tvd") );
				csvy.vs = atof( FetchField(res_set, "vs") );
				csvy.ca = atof( FetchField(res_set, "ca") );
				csvy.cd = atof( FetchField(res_set, "cd") );
				csvy.dl = atof( FetchField(res_set, "dl") );
				csvy.tot = atof( FetchField(res_set, "tot") );
				csvy.bot = atof( FetchField(res_set, "bot") );
				csvy.dip = atof( FetchField(res_set, "dip") );
				csvy.fault = atof( FetchField(res_set, "fault") );
				if(keycheckOK) {
					ddepth = csvy.depth - psvy.depth;
					dogleg = calcdl(&psvy, &csvy, propazm, depthunits);
					if(strcmp(pterm_method,cmprto)!=0){
						csvy.method = 6;
					}
					if(csvy.method==6 || csvy.method==7 || csvy.method==8 || ) {
						if(csvy.method==8) {
							tok=strtok(csvy.data, ","); if(tok!=NULL) t_csvy.vs=atof(tok);
							tok=strtok(NULL, ","); if(tok!=NULL) t_csvy.tpos=atof(tok);
							tok=strtok(NULL, ","); if(tok!=NULL) t_csvy.dip=atof(tok);
							tok=strtok(NULL, ","); if(tok!=NULL) t_csvy.fault=atof(tok);
							fnum=t_csvy.vs+t_csvy.tpos+t_csvy.dip+t_csvy.fault;
						} else if(csvy.method==7) {
							tok=strtok(csvy.data, ","); if(tok!=NULL) t_csvy.tot=atof(tok);
							tok=strtok(NULL, ","); if(tok!=NULL) t_csvy.vs=atof(tok);
							tok=strtok(NULL, ","); if(tok!=NULL) t_csvy.tpos=atof(tok);
							fnum=t_csvy.tot+t_csvy.vs+t_csvy.tpos;
						} else {
							tok=strtok(csvy.data, ","); if(tok!=NULL) t_csvy.tvd=atof(tok);
							tok=strtok(NULL, ","); if(tok!=NULL) t_csvy.vs=atof(tok);
							tok=strtok(NULL, ","); if(tok!=NULL) t_csvy.tpos=atof(tok);
							fnum=t_csvy.tvd+t_csvy.vs+t_csvy.tpos;
						}
						if(strcmp(pterm_method,cmprto)!=0 && csvy.id!=fsvy_id){
							csvy.vs=t_csvy.vs+deltacl;
							t_csvy.vs = csvy.vs;
						}
						if(csvy.method==6){
							sprintf(csvy.data, "%lf,%lf,%lf", csvy.tvd,csvy.vs,t_csvy.tpos);
						} else if(csvy.method==7){
							sprintf(csvy.data,"%lf,%lf,%lf",csvy.tot,csvy.vs,t_csvy.tpos);
						} else if(csvy.method==8){
							sprintf(csvy.data,"%lf,%lf,%lf,%lf",csvy.vs,t_csvy.tpos,csvy.dip,csvy.fault);
						}
						if(fnum<-.001 || fnum>.001) {
							if(csvy.method==8) {
								if(csvy.dip!=0.0)
									csvy.tot=psvy.tot+(-tan(t_csvy.dip/57.29578)*fabs(t_csvy.vs-psvy.vs));
								else csvy.tot=psvy.tot;
								csvy.bot=psvy.bot-(psvy.tot-csvy.tot);
								csvy.tvd=csvy.tot-t_csvy.tpos;
							}
							else if(csvy.method==7) {
								csvy.tvd=t_csvy.tot-t_csvy.tpos;
								if(csvy.vs-psvy.vs==0.0) csvy.dip=0.0;
								else csvy.dip=-degrees( atan( (csvy.tot-psvy.tot) / (csvy.vs-psvy.vs) ) );
							}
							else	csvy.tvd=t_csvy.tvd;
							projtva(&psvy, &csvy, propazm, depthunits);
						}
						csvy.cl=csvy.depth-psvy.depth;
					}
					else {
						if(csvy.method==0) {	// calculate using last dogleg
							ddepth=csvy.depth-psvy.depth;
							csvy.inc=psvy.inc+(dinc*ddepth);
							csvy.azm=psvy.azm+(dazm*ddepth);
						}
						calccurv(&psvy, &csvy, propazm, depthunits);
					}
				}
				else {
					csvy.tvd = -1; csvy.vs = -1; csvy.ns = -1;
					csvy.ew = -1; csvy.ca = -1; csvy.cd = -1;
					csvy.dl = -1; csvy.cl = -1;
				}
				if(csvy.method!=6 && csvy.method!=7 && csvy.method!=8){
					csvy.tpos=csvy.tot-csvy.tvd;
				}else{
					csvy.tpos = t_csvy.tpos;
				}

				// auto-delete projections that have expired
				printf("auto delting\n");
				if(strcmp(pterm_method,cmprto)==0){
					if( csvy.depth<=psvy.depth || (csvy.vs>100.0 && csvy.vs<=psvy.vs) ) {
						sprintf(query, "DELETE FROM projections WHERE id=%ld;", csvy.id);
						printf("%s\n", query);
						DoQuery(res_set2, query);

						printf("Delete: csvy.id:%ld, csvy.depth:%f, psvy.depth:%f, csvy.vs:%f, psvy.vs:%f\n",
						csvy.id,
						csvy.depth, psvy.depth,
						csvy.vs, psvy.vs);

						continue;
					}
				} else {
					printf("Doing consume\n");
					if(csvy.id!=fsvy_id){
						fprintf(stderr, "%f: adding to survey with vs of %f\n", deltacl,csvy.vs);
						if(csvy.vs >= fsvy_vs && csvy.id!=fsvy_id){
							sprintf(query,"delete from projections where id=%ld",csvy.id);
							DoQuery(res_set2,query);
						}
					}
				}
				if(csvy.method==8) {
					csvy.tot+=t_csvy.fault; csvy.bot+=t_csvy.fault;
					csvy.tvd+=t_csvy.fault;
					sprintf(query, "update projections set md=%lf,inc=%lf,tvd=%lf,tot=%lf,bot=%lf where id=%ld",
						csvy.depth, csvy.inc, csvy.tvd, csvy.tot, csvy.bot, csvy.id);
					DoQuery(res_set2, query);
				}
				else if(csvy.method==7) {
					sprintf(query, "update projections set md=%lf,inc=%lf,tot=%lf,bot=%lf,tvd=%lf,dip=%lf where id=%ld",
						csvy.depth, csvy.inc, csvy.tot, csvy.bot, csvy.tvd, csvy.dip, csvy.id);
					DoQuery(res_set2, query);
					printf("%s\ndip:%f\n", query, csvy.dip);
				}
				else if(csvy.method==6) {
					sprintf(query, "update projections set md=%lf,inc=%lf where id=%ld",
						csvy.depth, csvy.inc, csvy.id);
					DoQuery(res_set2, query);
				}
				if(csvy.method!=7) {
					// find our proposed new tot/bot
					csvy.tot=psvy.tot+(-tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs));
					csvy.bot=psvy.bot+(-tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs));
					csvy.tot+=csvy.fault;
					csvy.bot+=csvy.fault;

					sprintf(query, "update projections set tot=%lf,bot=%lf,tvd=%lf where id=%ld",
						csvy.tot, csvy.bot, csvy.tvd, csvy.id);
					DoQuery(res_set2, query);
				}
				sprintf(query, "UPDATE projections SET tvd=%lf,vs=%lf,ns=%lf,ew=%lf,ca=%lf,cd=%lf,dl=%lf,cl=%lf,data='%s' WHERE id=%ld;",
					csvy.tvd, csvy.vs, csvy.ns, csvy.ew, csvy.ca, csvy.cd, csvy.dl, csvy.cl,csvy.data, csvy.id);
				printf("%s\n",query);
				DoQuery(res_set2, query);

				// pre-calculate the deltas for dogleg projections
				deltamd=csvy.depth-psvy.depth;
				if(deltamd!=0.0) {
					dinc=(csvy.inc-psvy.inc)/deltamd;
					dazm=(csvy.azm-psvy.azm)/deltamd;
				} else dinc=dazm=0.0;

				memcpy(&psvy, &csvy, sizeof(T_SVY));
				if(DoQuery(res_set2, "COMMIT;")){
						fprintf(stderr, "%s: Error in commit query\n", argv[0]);
				}
			}
		}
	} // end of: if(bDoProjections)
	if(DoQuery(res_set2, "COMMIT;")){
		fprintf(stderr, "%s: Error in commit query\n", argv[0]);
	}
	FreeResult(res_set2);
	FreeResult(res_set);

	CloseDb();
	return 0;
}
