#include "/usr/include/postgresql/libpq-fe.h"
#ifndef DBIO_C
typedef struct {
    int numRows, currentRow;
	PGresult *pRes;
} ResultSet;
extern ResultSet resultSet;
extern ResultSet *res_set;
extern ResultSet resultSet2;
extern ResultSet *res_set2;
extern ResultSet resultSet3;
extern ResultSet *res_set3;

int OpenDb(char *prog, char *dbname, char *userName, char *userPass);
void CloseDb(void);

int DoQuery(ResultSet *pRes, char *stmt_str);
int FetchRow(ResultSet *pRes);
char *FetchField(ResultSet *pRes, char *name);
char *FetchFieldFromRow(ResultSet *pRes, int nrow, char *name);
void FreeResult(ResultSet *pRes);

#endif
