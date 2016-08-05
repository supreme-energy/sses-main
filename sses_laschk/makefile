CC = gcc
# INCLUDES=${shell mysql_config --include}
# LIBS=${shell mysql_config --libs}
# LIBS=-Wl,-Bsymbolic-functions -rdynamic -L/usr/lib/mysql -lmysqlclient -lm -lusb
INCLUDES=-I/usr/include/postgresql
LIBS=-L/usr/lib -lpq -lusb -lm

all: sses_laschk

key: sses_laschkwk

laschk.o: laschk.c
	$(CC) -c $(INCLUDES) laschk.c

dbio.o: dbio.c
	$(CC) -c $(INCLUDES) dbio.c

sses_laschk: laschk.o dbio.o
	$(CC) -o sses_laschk laschk.o dbio.o $(LIBS); cp sses_laschk /home/www-data/sses

sses_laschkwk: laschk.o dbio.o
	$(CC) -o sses_laschk laschk.o dbio.o ../../vendor/keylok/linux/keylok.o ../../vendor/keylok/linux/kfunc32.o $(LIBS); cp sses_laschk /home/www-data/sses

clean:
	rm -f sses_laschk laschk.o dbio.o

