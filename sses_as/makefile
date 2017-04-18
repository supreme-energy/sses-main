CC = gcc
# INCLUDES=${shell mysql_config --include}
# LIBS=${shell mysql_config --libs}
# LIBS=-Wl,-Bsymbolic-functions -rdynamic -L/usr/lib/mysql -lmysqlclient -lm -lusb
INCLUDES=-I/usr/include/postgresql
LIBS=-L/usr/lib -lpq -lusb -lm

all: sses_as

calccurve.o: calccurve.c
	$(CC) -c $(INCLUDES) calccurve.c

dbio.o: dbio.c
	$(CC) -c $(INCLUDES) dbio.c

sses_as: calccurve.o dbio.o
	$(CC) -o sses_as calccurve.o dbio.o ../../vendor/keylok/linux/keylok.o ../../vendor/keylok/linux/kfunc32.o $(LIBS); cp sses_as /home/www-data/sses

clean:
	rm -f sses_as calccurve.o dbio.o

