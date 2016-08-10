#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <math.h>

// #define USEKEYLOK 1
#ifdef USEKEYLOK
#include "../../vendor/keylok/linux/keylok.h"
#endif
#include "dbio.h"

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
	int plan, hide;
	int method;
};
float bitoffset=0.0;

typedef struct t_dataset T_DATASET;
struct t_dataset {
	int		id;
	float	fault, dip;
	float	startmd, endmd;
	float	starttvd, endtvd;
	float	startvs, endvs;
	float	startdepth, enddepth;
	float	tot, bot;
};
#define	MAX_DATASETS	1024
T_DATASET datasets[MAX_DATASETS];
int	datasetCount;
T_DATASET refDataset;

char tablename[1024];
char dbname[4095];
char cmdstr[4095];

int bDoSurveys=1;
int bDoJustSurveys=0;
float modelStartAt=0.0;
int cnt;
float depth, fault, tvd, vs, md;
float startdepth=0;
float enddepth=0;
float lastmd=0;
float lasttvd=0;
float lastvs=0;
float lastdepth=0;
float propazm;
float plantot, controltot;
float planbot, controlbot;
float dip, plandip, controldip;
int tablecount=0;
float projdip = 0.0;
int sgta_off=0;

float softwareVersion = 1.0;
int	keycheckOK = 0;
unsigned char softwareOptions = 0;
unsigned int keySerialNumber = 0;

ResultSet resultSet4;
ResultSet *res_svyin = &resultSet4;
ResultSet resultSet5;
ResultSet *res_svyout = &resultSet5;

ResultSet resultSet6;
ResultSet *res_commit = &resultSet6;

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
	if (courselength > 0) {
		if(depthunits==1) doglegseverity = (dogleg * 30.0) / courselength;
		else doglegseverity = (dogleg * 100.0) / courselength;
	}

	if (dogleg!=0.0) radius=(2.0/dogleg)*tan(dogleg/2.0);

	cSvy->tvd=pSvy->tvd+((courselength/2.0)*(cos(pInc)+cos(cInc))*radius);
	cSvy->ns=pSvy->ns+((courselength/2.0)*((sin(pInc)*cos(pAzm))+(sin(cInc)*cos(cAzm)))*radius);
	cSvy->ew=pSvy->ew+((courselength/2.0)*((sin(pInc)*sin(pAzm))+(sin(cInc)*sin(cAzm)))*radius);

	if (cSvy->ns != 0) cSvy->ca = atan2(cSvy->ew, cSvy->ns);
	else if (cSvy->ew > 0.0) cSvy->ca = M_PI;
	else cSvy->ca = -M_PI;

	if (cSvy->ca != 0.0) cSvy->cd = fabs(cSvy->ew / sin(cSvy->ca));
	else cSvy->cd = cSvy->ns;

	cSvy->vs = cSvy->cd * cos(cSvy->ca - propazm);
	cSvy->dl = degrees(doglegseverity);
	cSvy->build = ((cSvy->inc - pSvy->inc) * 100) / courselength;
	cSvy->turn = ((cSvy->azm - pSvy->azm) * 100) / courselength;
	cSvy->cl = courselength;
}

/*****************************************************************************/

void barfAndDie(char *progname) {
	printf("Usage: %s {-d dbname} [-s startDepth] [-e endDepth] [--nosurveys || --justsurveys]\n\
", progname);
	exit(1);
}

/*****************************************************************************/

void initSurveys(float t, float b, float d) {
	char q[256];
	int i, id;
	float ftot=t;
	float fbot=b;
	float fdip=d;
	float flastvs;

	// printf("initializing surveys with dip %.2f\n", d);

	sprintf(q, "SELECT * FROM surveys ORDER BY md ASC;");
	if(DoQuery(res_svyin, q)) return;
	i=0;
	DoQuery(res_svyout, "BEGIN TRANSACTION;");
	while(FetchRow(res_svyin)) {
		id=atoi(FetchField(res_svyin, "id"));
		vs=atof(FetchField(res_svyin, "vs"));
		fdip=atof(FetchField(res_svyin,"dip"));
		if(i==0) flastvs=vs;
		// ftot+=(-tan(fdip/57.29578)*(vs-flastvs));
		// fbot+=(-tan(fdip/57.29578)*(vs-flastvs));
		sprintf(q,
			"UPDATE surveys SET tot=%f,bot=%f,dip=%f WHERE id=%d;",
			ftot, fbot, fdip, id);
		if(DoQuery(res_svyout, q)) {
			FreeResult(res_svyin);
			return;
		}
		flastvs=vs;
		i++;
	}
	DoQuery(res_svyout, "COMMIT;");
	FreeResult(res_svyout);
	FreeResult(res_svyin);
}

/*****************************************************************************/

void	ReadDatasets(void) {
	int i;

	datasetCount=0;
	sprintf(cmdstr, "SELECT * FROM welllogs ORDER BY startmd ASC;");
	if (DoQuery(res_svyin, cmdstr)) {
		fprintf(stderr, "ReadDatasets: Error in select query for welllogs: %s\n", cmdstr);
		CloseDb();
		exit (-1);
	}
	while(FetchRow(res_svyin)) {
		datasets[datasetCount].id=atoi( FetchField(res_svyin, "id") );
		datasets[datasetCount].fault=atof( FetchField(res_svyin, "fault") );
		datasets[datasetCount].dip=atof( FetchField(res_svyin, "dip") );
		datasets[datasetCount].startmd=atof(FetchField(res_svyin, "startmd"));
		datasets[datasetCount].endmd=atof(FetchField(res_svyin, "endmd"));
		datasets[datasetCount].starttvd=atof(FetchField(res_svyin, "starttvd"));
		datasets[datasetCount].endtvd=atof(FetchField(res_svyin, "endtvd"));
		datasets[datasetCount].startvs=atof(FetchField(res_svyin, "startvs"));
		datasets[datasetCount].endvs=atof(FetchField(res_svyin, "endvs"));
		datasets[datasetCount].startdepth=atof(FetchField(res_svyin, "startdepth"));
		datasets[datasetCount].enddepth=atof(FetchField(res_svyin, "enddepth"));
		datasets[datasetCount].tot=atof(FetchField(res_svyin, "tot"));
		datasets[datasetCount].bot=atof(FetchField(res_svyin, "bot"));
		datasetCount++;
	}
	FreeResult(res_svyin);
}

void PrintDatasets(void) {
	int i;
	for(i=0; i < datasetCount; i++) {
		printf("ds:%d %.2f to %.2f fault: %.2f\n",
			i,
			datasets[i].startmd,
			datasets[i].endmd,
			datasets[i].fault
		);
	}
}

/*****************************************************************************/

int FetchFaultTotal(T_SVY *pSvy, T_SVY *cSvy) {
	int i;
	int wlid=-1;
	float ffault=0.0;
	for(i=0; i < datasetCount; i++) {
		if(datasets[i].startmd >= pSvy->depth) {
			if (datasets[i].startmd <= cSvy->depth) {
				wlid=datasets[i].id;
				ffault+=datasets[i].fault;
				// printf("wlid:%d ", wlid);
			}
		}
	}
	cSvy->fault=ffault;
	// printf("fault:%.2f ->", ffault);
	return wlid;
}

/*****************************************************************************/

void FetchModeledTot(T_SVY *pSvy, T_SVY *cSvy) {
	int i, j;
	float test;
	float fdip, ftot, fendtot, fendbot, fendvs, fvsrange;
	float ffault=0.0;
	float diptotal=0.0;
	// printf("Survey depth:%.2f ", cSvy->depth);
	for(i=0,j=0; i<datasetCount; i++) {
		if(datasets[i].startmd <= cSvy->depth && datasets[i].endmd > pSvy->depth) {
			fendvs=datasets[i].endvs;
			fendtot=datasets[i].tot;
			fendbot=datasets[i].bot;
			fdip=datasets[i].dip;
			// printf("found dip %.2f at %.2f ", fdip, datasets[i].startmd);
			diptotal+=fdip;
			j++;
		}
	}
	if(j>0) diptotal/=(float)j;
	// printf("diptotal:%.2f\n", diptotal);
	fvsrange=(fendvs-pSvy->vs);
	ftot=1.0;
	if(fvsrange!=0.0) ftot=(fendtot-pSvy->tot)/fvsrange;
	cSvy->tot=pSvy->tot + (cSvy->vs-pSvy->vs)*ftot;
	cSvy->bot=pSvy->bot + (cSvy->vs-pSvy->vs)*ftot;

	cSvy->dip=diptotal;
}
/*****************************************************************************/

int main(int argc, char * argv[])
{
	int method=0;
	float fnum;
	float ftot, fbot;
	float fstartmd;
	int  i, id, tableid;
	int wlid, lastwlid;
	float ffault;
	T_SVY psvy, csvy, refsvy;

	depth=md=tvd=vs=fault=dip=0;
	planbot=plantot=0;

	keycheckOK=1;
	// CheckForValidKey();
	if(!keycheckOK) {
		printf("Error: No security key found!\n");
		return -1;
	}

	if(argc<2)	barfAndDie(argv[0]);

	// process all the arguments
	strcpy(dbname, "\0");
	for(i=1; i < argc; i++)
	{
		if(!strcmp(argv[i], "-d"))
			strcpy(dbname, argv[++i]);
		else if(!strcmp(argv[i], "-s"))
			modelStartAt=atof(argv[++i]);
		else if(!strcmp(argv[i], "--nosurveys"))
			bDoSurveys=0;
		else if(!strcmp(argv[i], "--justsurveys"))
			bDoJustSurveys=1;
		else {
			printf("Error in parameter: %s\n", argv[i]);
			barfAndDie(argv[0]);
		}
	}

	if (OpenDb(argv[0], dbname, "umsdata", "umsdata") != 0)
	{
		fprintf(stderr, "Failed to open database\n");
		exit(-1);
	}

	// fetch the well information
	plandip=plantot=planbot=0.0;
	if (DoQuery(res_set, "SELECT * FROM appinfo LIMIT 1;")) {
		fprintf(stderr, "%s: Error in select query for wellinfo\n", argv[0]);
		CloseDb();
		exit (-1);
	}
	if(FetchRow(res_set)){
		sgta_off=atoi(FetchField(res_set,"sgta_off"));
		FreeResult(res_set);
	}

	if (DoQuery(res_set, "SELECT * FROM wellinfo LIMIT 1;")) {
		fprintf(stderr, "%s: Error in select query for wellinfo\n", argv[0]);
		CloseDb();
		exit (-1);
	}
	if(FetchRow(res_set)) {
		plantot=atof(FetchField(res_set, "tot"));
		planbot=atof(FetchField(res_set, "bot"));
		propazm=atof(FetchField(res_set, "propazm"));
		projdip=atof(FetchField(res_set, "projdip"));
		bitoffset = atoi( FetchField(res_set, "bitoffset") );
		propazm=radians(propazm);
		FreeResult(res_set);
	}

	controldip=controltot=controlbot=0.0;
	if (DoQuery(res_set, "SELECT * FROM controllogs LIMIT 1;")) {
		fprintf(stderr, "%s: Error in select query for controllogs\n", argv[0]);
		CloseDb();
		exit (-1);
	}
	if(FetchRow(res_set)) {
		controltot=atof(FetchField(res_set, "tot"));
		controlbot=atof(FetchField(res_set, "bot"));
		controldip=atof(FetchField(res_set, "dip"));
		FreeResult(res_set);
	}

	// cache all the datasets 
	// ReadDatasets();
	// PrintDatasets();


if(!bDoJustSurveys) {
	/*
	sprintf(cmdstr, "SELECT count(id) FROM welllogs WHERE startmd>=%f;", modelStartAt);
	if (DoQuery(res_set, cmdstr)) {
		fprintf(stderr, "%s: Error in select query for welllogs\n", argv[0]);
		CloseDb();
		exit (-1);
	}
	else if(FetchRow(res_set)) {
			i=atoi( FetchField(res_set, "count") );
			sprintf(cmdstr, "SELECT * FROM welllogs ORDER BY startmd ASC LIMIT %d;", i+2);
			printf("%s\n", cmdstr);
	}
	else
	*/
	sprintf(cmdstr, "SELECT * FROM welllogs ORDER BY startmd ASC;");

	if (DoQuery(res_set, cmdstr)) {
		fprintf(stderr, "%s: Error in select query for welllogs\n", argv[0]);
		CloseDb();
		exit (-1);
	}
	else {
		initSurveys(plantot, planbot, 0);
		tablecount=0;
		DoQuery(res_commit, "BEGIN TRANSACTION;");
		while(FetchRow(res_set)) {
			strcpy(tablename, FetchField(res_set, "tablename"));
			tableid=atoi( FetchField(res_set, "id") );
			fault=atof( FetchField(res_set, "fault") );
			dip=atof( FetchField(res_set, "dip") );
			fstartmd=atof(FetchField(res_set, "startmd"));
			if(dip<-89.9)	dip=-89.9;
			if(dip>89.9)	dip=89.9;
			sprintf(cmdstr, "SELECT * FROM \"%s\" ORDER BY md ASC;", tablename);
			if(DoQuery(res_set2, cmdstr)==0) {
				if(FetchNumRows(res_set2)>0) {
					cnt=0;
					while(FetchRow(res_set2)) {
						id = atoi(FetchField(res_set2, "id"));
						tvd = atof(FetchField(res_set2, "tvd"));
						vs = atof(FetchField(res_set2, "vs"));
						md = atof(FetchField(res_set2, "md"));
						// first point initialization
						if(cnt==0 && tablecount==0) {
								lastvs=vs;
								lasttvd=tvd;
								lastmd=md;
								lastdepth=tvd;
						}
						// printf("\tlasttvd:%.3f lastvs:%.3f lastdepth:%.3f\n", lasttvd, lastvs, lastdepth);
						depth=tvd-(-tan(dip/57.29578)*fabs(vs-lastvs))-fault-(lasttvd-lastdepth);
						if(fstartmd>=modelStartAt) {
							sprintf(cmdstr, "UPDATE \"%s\" SET depth=%f WHERE id=%d;", tablename, depth, id);
							DoQuery(res_commit, cmdstr);
						}
						if(cnt==0) startdepth=depth;
						cnt++;
					}
					FreeResult(res_set2);
				}
				ftot=tvd+(controltot-depth);
				fbot=tvd+(controlbot-depth);
				// ftot=tvd+(plantot-depth);
				// fbot=tvd+(planbot-depth);
				lastvs=vs;
				lasttvd=tvd;
				lastdepth=depth;

				if(fstartmd>=modelStartAt) {
					sprintf(cmdstr,
					"UPDATE welllogs SET tot=%f,bot=%f,startdepth=%f,enddepth=%f WHERE id=%d;",
						ftot, fbot, startdepth, lastdepth, tableid);
					DoQuery(res_set3, cmdstr);
					FreeResult(res_set3);
				}
			}
			lastmd=md;
			tablecount++;
		}
		DoQuery(res_commit, "COMMIT;");
		FreeResult(res_commit);
		FreeResult(res_set);
	}

} // end: if(!bDoJustSurveys)
// cache all the datasets: again
ReadDatasets();

	// graphics pass only
	if(!bDoSurveys) {
		CloseDb();
		return 0 ;
	}

	// survey pass
	sprintf(cmdstr, "SELECT * FROM surveys WHERE md>=%f ORDER BY md;", modelStartAt);
	if (DoQuery(res_set, cmdstr)) {
		fprintf(stderr, "%s: Error in select query for surveys\n", argv[0]);
		CloseDb();
		exit (-1);
	}
	else {

		i=0;
		wlid=lastwlid=-1;
		while(FetchRow(res_set)) {
			DoQuery(res_commit, "BEGIN TRANSACTION;");
			id=atoi( FetchField(res_set, "id") );
			if(i<=0) {
				psvy.depth = atof( FetchField(res_set, "md") );
				psvy.tvd = atof( FetchField(res_set, "tvd") );
				psvy.vs = atof( FetchField(res_set, "vs") );
				psvy.tot =plantot;
				psvy.bot = plantot;
				psvy.plan = atof( FetchField(res_set, "plan") );
				if(sgta_off){
					psvy.dip=atof(FetchField(res_set,"dip"));
					psvy.fault=atof(FetchField(res_set,"fault"));

					psvy.tot= -tan(psvy.dip/57.29578)*psvy.vs+plantot+psvy.fault;

					psvy.bot=psvy.tot;
					sprintf(cmdstr, "UPDATE surveys SET fault=%f,dip=%f,tot=%f,bot=%f WHERE id=%d;",
										psvy.fault, psvy.dip, psvy.tot, psvy.bot, id);
					DoQuery(res_commit, cmdstr);

				}
				memcpy(&refsvy, &psvy, sizeof(T_SVY));	// save very first hangoff survey
				fault=ffault=0.0; // clear the fault accumulator
				DoQuery(res_commit, "COMMIT;");
			} else {
				csvy.depth = atof( FetchField(res_set, "md") );
				csvy.tvd = atof( FetchField(res_set, "tvd") );
				csvy.vs = atof( FetchField(res_set, "vs") );
				csvy.plan = atof( FetchField(res_set, "plan") );
				// find our proposed new tot/bot
				if(!sgta_off){
					if(csvy.plan==0) {
						csvy.tot=psvy.tot+(-tan(controldip/57.29578)*(csvy.vs-psvy.vs));
						csvy.bot=psvy.bot+(-tan(controldip/57.29578)*(csvy.vs-psvy.vs));
					}
					else {
						csvy.tot=psvy.tot+(-tan(projdip/57.29578)*(csvy.vs-psvy.vs));
						csvy.bot=psvy.bot+(-tan(projdip/57.29578)*(csvy.vs-psvy.vs));
					}
					csvy.fault=0.0;
					// see if a welllog overrides the proposed from above
					if(csvy.plan==0) {
						wlid=FetchFaultTotal(&psvy, &csvy);
						printf("%i\n",wlid);
						if(wlid!=lastwlid && wlid>0) {
							memcpy(&refsvy, &psvy, sizeof(T_SVY));	// use previous svy for new reference
							refsvy.tot+=csvy.fault;	// null out faults
							refsvy.bot+=csvy.fault;
							lastwlid=wlid;
						}
						FetchModeledTot(&refsvy, &csvy);
						// printf("FetchModeledTot: md:%.2lf dip:%.2f\n", csvy.depth, csvy.dip);
					} else {
						// printf ("Bit Projection  ");
						csvy.dip=projdip;
					}
				} else {
					csvy.dip = atof(FetchField(res_set,"dip"));
					csvy.fault = atof(FetchField(res_set,"fault"));
					csvy.tot = -tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs)+psvy.tot+csvy.fault;
					csvy.bot = csvy.tot;

				}
				// calculate the dip for this survey section
				/* if(csvy.vs-refsvy.vs==0.0) csvy.dip=0.0;
				else {
					if(csvy.plan==0) {
						csvy.dip=-degrees( atan( (csvy.tot-refsvy.tot) / fabs(csvy.vs-refsvy.vs) ) );
					} else { // bit projection
						csvy.dip=-degrees( atan( (csvy.tot-psvy.tot) / fabs(csvy.vs-psvy.vs) ) );
					}
				} */
				sprintf(cmdstr, "UPDATE surveys SET fault=%f,dip=%f,tot=%f,bot=%f WHERE id=%d;",
					csvy.fault, csvy.dip, csvy.tot, csvy.bot, id);
				printf("%s\n",cmdstr);
				memcpy(&psvy, &csvy, sizeof(T_SVY));
				DoQuery(res_commit, cmdstr);
				DoQuery(res_commit, "COMMIT;");
			}
			i++;
		}

		FreeResult(res_commit);
		FreeResult(res_set);
	}

	// projections
	/* let calcurve take care of projection modeling
	sprintf(cmdstr, "SELECT * FROM projections ORDER BY md ASC;",
		modelStartAt);
	if (DoQuery(res_set, cmdstr)) {
		fprintf(stderr, "%s: Error in select query for projections\n", argv[0]);
	}
	else {
		DoQuery(res_commit, "BEGIN TRANSACTION;");
		while(FetchRow(res_set)) {
			id=atoi( FetchField(res_set, "id") );
			csvy.method=atoi( FetchField(res_set, "method") );
			csvy.depth = atof( FetchField(res_set, "md") );
			csvy.tvd = atof( FetchField(res_set, "tvd") );
			csvy.tot = atof( FetchField(res_set, "tot") );
			csvy.bot = atof( FetchField(res_set, "bot") );
			csvy.vs = atof( FetchField(res_set, "vs") );
			csvy.dip = atof( FetchField(res_set, "dip") );
			csvy.fault = atof( FetchField(res_set, "fault") );
			if(csvy.method==7) {
				// calculate the dip for this survey section
				if(csvy.vs-refsvy.vs==0.0) csvy.dip=0.0;
				else csvy.dip=-degrees( atan( (csvy.tot-psvy.tot) / (csvy.vs-psvy.vs) ) );
			} else if(csvy.method!=8) {
				// find our proposed new tot/bot
				csvy.tot=psvy.tot+(-tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs));
				csvy.bot=psvy.bot+(-tan(csvy.dip/57.29578)*(csvy.vs-psvy.vs));
				csvy.tot+=csvy.fault;
				csvy.bot+=csvy.fault;
			}
			if(csvy.method!=8) { // sses_cc (calccurve.c) performs this placement
				sprintf(cmdstr, "UPDATE projections SET fault=%f,dip=%f,tot=%f,bot=%f WHERE id=%d;",
					csvy.fault, csvy.dip, csvy.tot, csvy.bot, id);
				DoQuery(res_commit, cmdstr);
			}
			memcpy(&psvy, &csvy, sizeof(T_SVY));
		}
		DoQuery(res_commit, "COMMIT;");
		FreeResult(res_commit);
		FreeResult(res_set);
	}
	*/

	CloseDb();
	return 0 ;
}

