#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <string.h>
#include "dbio.h"

#define SELECT_INFO_QUERY "SELECT * FROM infotable;"
#define SELECT_IDLIST_QUERY "SELECT * FROM idtable WHERE export = 1 ORDER BY las_order;"
#define SELECT_SURVEYS_QUERY "SELECT * FROM surveytable ORDER BY depth;"
#define SELECT_TABLEINFO_QUERY "SELECT * FROM idtable WHERE logenable=true;"

static PGconn *conn;
static PGresult *idtable_res_set;
ResultSet resultSet;
ResultSet *res_set = &resultSet;
ResultSet resultSet2;
ResultSet *res_set2 = &resultSet2;

static char errorbuf[1024];
static char msgBuf[1024];


/*****************************************************************************/

int OpenDb(char *prog, char *dbname, char *userName, char *userPass)
{
	char dbn[1024];
	res_set->numRows = res_set->currentRow = 0;
	sprintf(dbn, "dbname=%s user=%s password=%s", dbname, userName, userPass);

	conn = PQconnectdb(dbn);
	if (PQstatus(conn) != CONNECTION_OK) {
		printf("%s: Couldn't connect to engine!\n%s", prog, PQerrorMessage(conn));
		return(-1);
	}
	return 0;
}

/*****************************************************************************/

void CloseDb(void)
{
	PQfinish(conn);
}

/*****************************************************************************/

int FetchNumRows(ResultSet *pResultSet) {
	return PQntuples(pResultSet->pRes);
}

/*****************************************************************************/

int DoQuery(ResultSet *pResultSet, char *stmt_str)
{
	pResultSet->pRes = PQexec(conn, stmt_str);
	if (PQresultStatus(pResultSet->pRes) != PGRES_TUPLES_OK &&
		PQresultStatus(pResultSet->pRes) != PGRES_COMMAND_OK)
	{
		sprintf(errorbuf, "Could not execute statement: %s\nreturned:%s\n",
			stmt_str,
			PQerrorMessage(conn));
		fprintf(stderr, "%s", errorbuf);
		// fprintf(stderr, "%s", errorbuf);
		PQclear(pResultSet->pRes);
		return -1;
	}

	pResultSet->numRows = PQntuples(pResultSet->pRes);
	pResultSet->currentRow = -1;
	return 0;
}

/*****************************************************************************/

int FetchRow(ResultSet *pResultSet)
{
	if(pResultSet->pRes==NULL)	return 0;
	if(pResultSet->currentRow >= pResultSet->numRows-1)
		return 0;
	pResultSet->currentRow++;
	return 1;
}

/*****************************************************************************/

char *FetchFieldFromRow(ResultSet *pResultSet, int nrow, char *name)
{
	int nfield;
	static char val[2048];
	nfield = PQfnumber(pResultSet->pRes, name);
	if(nfield >= 0 && nrow < pResultSet->numRows - 1) {
		strcpy(val, PQgetvalue(pResultSet->pRes, nrow, nfield));
		return val;
	}
	return NULL;
}

/*****************************************************************************/

char *FetchField(ResultSet *pResultSet, char *name)
{
	int n;
	static char val[2048];
	n = PQfnumber(pResultSet->pRes, name);
	if(n >= 0) {
		strcpy(val, PQgetvalue(pResultSet->pRes, pResultSet->currentRow, n));
		return val;
	}
	return NULL;
}

/*****************************************************************************/

void FreeResult(ResultSet *pResultSet)
{
	if(pResultSet->pRes)
		PQclear(pResultSet->pRes);
	pResultSet->pRes=NULL;
}

/*****************************************************************************/
