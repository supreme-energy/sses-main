--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: permission_level; Type: TYPE; Schema: public; Owner: umsdata
--

CREATE TYPE permission_level AS ENUM (
    'READ_ONLY',
    'READ_WRITE',
    'ADMIN',
    'SUPER_USER'
);


ALTER TYPE public.permission_level OWNER TO umsdata;

--
-- Name: priv_level; Type: TYPE; Schema: public; Owner: umsdata
--

CREATE TYPE priv_level AS ENUM (
    'READ_ONLY',
    'READ_WRITE',
    'ADMIN',
    'SUPER_USER'
);


ALTER TYPE public.priv_level OWNER TO umsdata;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: dbindex; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE dbindex (
    id integer NOT NULL,
    dbname text,
    realname text,
    entity_id integer
);


ALTER TABLE public.dbindex OWNER TO umsdata;

--
-- Name: dbindex_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE dbindex_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dbindex_id_seq OWNER TO umsdata;

--
-- Name: dbindex_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE dbindex_id_seq OWNED BY dbindex.id;


--
-- Name: dbinfo; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE dbinfo (
    id integer NOT NULL,
    lastid integer DEFAULT 0 NOT NULL,
    entity_id integer
);


ALTER TABLE public.dbinfo OWNER TO umsdata;

--
-- Name: dbinfo_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE dbinfo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dbinfo_id_seq OWNER TO umsdata;

--
-- Name: dbinfo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE dbinfo_id_seq OWNED BY dbinfo.id;


--
-- Name: entities; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE entities (
    id integer NOT NULL,
    entity_name text NOT NULL
);


ALTER TABLE public.entities OWNER TO umsdata;

--
-- Name: server_info; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE server_info (
    id integer NOT NULL,
    lan_addr text,
    wan_addr text,
    on_lan boolean DEFAULT true,
    reports_lan text,
    reports_wan text
);


ALTER TABLE public.server_info OWNER TO umsdata;

--
-- Name: server_info_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE server_info_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.server_info_id_seq OWNER TO umsdata;

--
-- Name: server_info_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE server_info_id_seq OWNED BY server_info.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    email text NOT NULL,
    password text NOT NULL,
    entity_id integer NOT NULL,
    plevel permission_level NOT NULL
);


ALTER TABLE public.users OWNER TO umsdata;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO umsdata;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE ONLY dbindex ALTER COLUMN id SET DEFAULT nextval('dbindex_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE ONLY dbinfo ALTER COLUMN id SET DEFAULT nextval('dbinfo_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE ONLY server_info ALTER COLUMN id SET DEFAULT nextval('server_info_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- Data for Name: dbindex; Type: TABLE DATA; Schema: public; Owner: umsdata
--

COPY dbindex (id, dbname, realname, entity_id) FROM stdin;
219	sgta_219	SC-Tom-153-98-1514-H1-SSES-2013-280 (H2)	\N
279	sgta_279	JLH A15 - Sean	\N
281	sgta_281	5 Mile Creek - Sean	\N
282	sgta_282	Go Garfield - Sean	\N
283	sgta_283	Prost C6 - Sean	\N
284	sgta_284	Prost Unit B #5H - Sean	\N
285	sgta_285	Pohribnak - Sean	\N
286	sgta_286	SC-Nelson - Sean	\N
271	sgta_271	JHLA15Endresen.backup	\N
\.


--
-- Name: dbindex_id_seq; Type: SEQUENCE SET; Schema: public; Owner: umsdata
--

SELECT pg_catalog.setval('dbindex_id_seq', 286, true);


--
-- Data for Name: dbinfo; Type: TABLE DATA; Schema: public; Owner: umsdata
--

COPY dbinfo (id, lastid, entity_id) FROM stdin;
1	271	1
\.


--
-- Name: dbinfo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: umsdata
--

SELECT pg_catalog.setval('dbinfo_id_seq', 1, true);


--
-- Data for Name: entities; Type: TABLE DATA; Schema: public; Owner: umsdata
--

COPY entities (id, entity_name) FROM stdin;
2	Hess
1	SSES
3	Cinco Ranch
\.


--
-- Data for Name: server_info; Type: TABLE DATA; Schema: public; Owner: umsdata
--

COPY server_info (id, lan_addr, wan_addr, on_lan, reports_lan, reports_wan) FROM stdin;
1	192.168.1.4:83	sgta.us:83	t	192.168.1.7	sgta.us
\.


--
-- Name: server_info_id_seq; Type: SEQUENCE SET; Schema: public; Owner: umsdata
--

SELECT pg_catalog.setval('server_info_id_seq', 1, true);


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: umsdata
--

COPY users (id, email, password, entity_id, plevel) FROM stdin;
2	cmcginnis@sses.us	820eb5b696ea2a657c0db1e258dc7d81	1	SUPER_USER
1	cbergman@sses.us	962012d09b8170d912f0669f6d7d9d07	3	ADMIN
\.


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: umsdata
--

SELECT pg_catalog.setval('users_id_seq', 1, false);


--
-- Name: dbindex_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY dbindex
    ADD CONSTRAINT dbindex_pkey PRIMARY KEY (id);


--
-- Name: dbinfo_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY dbinfo
    ADD CONSTRAINT dbinfo_pkey PRIMARY KEY (id);


--
-- Name: entities_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY entities
    ADD CONSTRAINT entities_pkey PRIMARY KEY (id);


--
-- Name: server_info_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY server_info
    ADD CONSTRAINT server_info_pkey PRIMARY KEY (id);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

