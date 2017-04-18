--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: CLD-9; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE "CLD-9" (
    id integer NOT NULL,
    md double precision,
    tvd double precision,
    vs double precision,
    value double precision,
    hide smallint DEFAULT 0 NOT NULL
);


ALTER TABLE public."CLD-9" OWNER TO umsdata;

--
-- Name: CLD-9_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE "CLD-9_id_seq"
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public."CLD-9_id_seq" OWNER TO umsdata;

--
-- Name: CLD-9_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE "CLD-9_id_seq" OWNED BY "CLD-9".id;


--
-- Name: addforms; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE addforms (
    id integer NOT NULL,
    label text DEFAULT ''::text NOT NULL,
    color text DEFAULT '0000ff'::text NOT NULL,
    selected integer DEFAULT 0 NOT NULL,
    md double precision DEFAULT 0 NOT NULL,
    tvd double precision DEFAULT 0 NOT NULL,
    vs double precision DEFAULT 0 NOT NULL,
    dip double precision DEFAULT 0 NOT NULL,
    fault double precision DEFAULT 0 NOT NULL,
    tot double precision DEFAULT 0 NOT NULL,
    bot double precision DEFAULT 0 NOT NULL,
    thickness double precision DEFAULT 0 NOT NULL
);


ALTER TABLE public.addforms OWNER TO umsdata;

--
-- Name: addforms_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE addforms_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.addforms_id_seq OWNER TO umsdata;

--
-- Name: addforms_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE addforms_id_seq OWNED BY addforms.id;


--
-- Name: addformsdata; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE addformsdata (
    id integer NOT NULL,
    infoid integer DEFAULT (-1) NOT NULL,
    svyid integer DEFAULT (-1) NOT NULL,
    projid integer DEFAULT (-1) NOT NULL,
    md double precision DEFAULT 0 NOT NULL,
    tvd double precision DEFAULT 0 NOT NULL,
    vs double precision DEFAULT 0 NOT NULL,
    dip double precision DEFAULT 0 NOT NULL,
    fault double precision DEFAULT 0 NOT NULL,
    tot double precision DEFAULT 0 NOT NULL,
    bot double precision DEFAULT 0 NOT NULL,
    thickness double precision DEFAULT 0 NOT NULL
);


ALTER TABLE public.addformsdata OWNER TO umsdata;

--
-- Name: addformsdata_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE addformsdata_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.addformsdata_id_seq OWNER TO umsdata;

--
-- Name: addformsdata_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE addformsdata_id_seq OWNED BY addformsdata.id;


--
-- Name: appinfo; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE appinfo (
    id integer NOT NULL,
    version double precision,
    scaleright double precision DEFAULT 0,
    zoom numeric DEFAULT 3 NOT NULL,
    bias double precision DEFAULT 0 NOT NULL,
    scale double precision DEFAULT 1 NOT NULL,
    tot double precision DEFAULT 0 NOT NULL,
    bot double precision DEFAULT 0 NOT NULL,
    viewallds integer DEFAULT 1 NOT NULL,
    viewrotds integer DEFAULT 0 NOT NULL,
    surveysort text DEFAULT 'asc'::text NOT NULL,
    dataset integer DEFAULT 1 NOT NULL,
    tablename text DEFAULT ''::text NOT NULL,
    viewadjds integer DEFAULT 1 NOT NULL,
    showxy integer DEFAULT 0 NOT NULL,
    sgtastart double precision DEFAULT 0.0 NOT NULL,
    sgtaend double precision DEFAULT 100.0 NOT NULL,
    sgtacutin double precision DEFAULT 0.0 NOT NULL,
    sgtacutoff double precision DEFAULT 99999.0 NOT NULL,
    lastptype text DEFAULT 'LAT'::text NOT NULL,
    lastmtype text DEFAULT 'INC'::text NOT NULL,
    uselogscale integer DEFAULT 0 NOT NULL,
    viewdspcnt integer DEFAULT 0 NOT NULL,
    dataavg integer DEFAULT 0 NOT NULL,
    dscache_dip double precision DEFAULT 0.0 NOT NULL,
    dscache_fault double precision DEFAULT 0.0 NOT NULL,
    dscache_bias double precision DEFAULT 0.0 NOT NULL,
    dscache_scale double precision DEFAULT 1.0 NOT NULL,
    dscache_freeze integer DEFAULT 0 NOT NULL,
    dscache_md double precision DEFAULT 99999.0 NOT NULL,
    dscache_plotstart double precision DEFAULT 0.0 NOT NULL,
    dscache_plotend double precision DEFAULT 99999.0 NOT NULL,
    dmod integer DEFAULT 10 NOT NULL
);


ALTER TABLE public.appinfo OWNER TO umsdata;

--
-- Name: appinfo_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE appinfo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.appinfo_id_seq OWNER TO umsdata;

--
-- Name: appinfo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE appinfo_id_seq OWNED BY appinfo.id;


--
-- Name: controllogs; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE controllogs (
    id integer NOT NULL,
    tablename text,
    startmd numeric,
    endmd numeric,
    realname text,
    dip double precision DEFAULT 0 NOT NULL,
    tot double precision DEFAULT 0 NOT NULL,
    bot double precision DEFAULT 0 NOT NULL
);


ALTER TABLE public.controllogs OWNER TO umsdata;

--
-- Name: controllogs_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE controllogs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.controllogs_id_seq OWNER TO umsdata;

--
-- Name: controllogs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE controllogs_id_seq OWNED BY controllogs.id;


--
-- Name: edatalogs; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE edatalogs (
    id integer NOT NULL,
    colnum integer DEFAULT 0 NOT NULL,
    tablename text DEFAULT ''::text NOT NULL,
    label text DEFAULT 'edata'::text NOT NULL,
    scalelo double precision DEFAULT 0.0 NOT NULL,
    scalehi double precision DEFAULT 300.0 NOT NULL,
    enabled integer DEFAULT 0 NOT NULL,
    logscale integer DEFAULT 0 NOT NULL,
    color text DEFAULT '#0000ff'::text NOT NULL
);


ALTER TABLE public.edatalogs OWNER TO umsdata;

--
-- Name: edatalogs_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE edatalogs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.edatalogs_id_seq OWNER TO umsdata;

--
-- Name: edatalogs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE edatalogs_id_seq OWNED BY edatalogs.id;


--
-- Name: emailinfo; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE emailinfo (
    id integer NOT NULL,
    smtp_from text DEFAULT ''::text NOT NULL,
    smtp_server text DEFAULT ''::text NOT NULL,
    smtp_login text DEFAULT ''::text NOT NULL,
    smtp_password text DEFAULT ''::text NOT NULL,
    enabled integer DEFAULT 1 NOT NULL,
    smtp_message text DEFAULT 'Email report'::text NOT NULL
);


ALTER TABLE public.emailinfo OWNER TO umsdata;

--
-- Name: emailinfo_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE emailinfo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.emailinfo_id_seq OWNER TO umsdata;

--
-- Name: emailinfo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE emailinfo_id_seq OWNED BY emailinfo.id;


--
-- Name: emaillist; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE emaillist (
    id integer NOT NULL,
    name text DEFAULT ''::text NOT NULL,
    email text DEFAULT ''::text NOT NULL,
    enabled integer DEFAULT 1 NOT NULL,
    phone text DEFAULT ''::text NOT NULL,
    cat text DEFAULT 'Operator'::text NOT NULL
);


ALTER TABLE public.emaillist OWNER TO umsdata;

--
-- Name: emaillist_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE emaillist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.emaillist_id_seq OWNER TO umsdata;

--
-- Name: emaillist_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE emaillist_id_seq OWNED BY emaillist.id;


--
-- Name: projections; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE projections (
    id integer NOT NULL,
    hide integer DEFAULT 0 NOT NULL,
    method integer DEFAULT 0 NOT NULL,
    data text DEFAULT '0,0,0'::text NOT NULL,
    md double precision DEFAULT 0.0 NOT NULL,
    inc double precision DEFAULT 0.0 NOT NULL,
    azm double precision DEFAULT 0.0 NOT NULL,
    tvd double precision DEFAULT 0.0 NOT NULL,
    vs double precision DEFAULT 0.0 NOT NULL,
    ns double precision DEFAULT 0.0 NOT NULL,
    ew double precision DEFAULT 0.0 NOT NULL,
    ca double precision DEFAULT 0.0 NOT NULL,
    cd double precision DEFAULT 0.0 NOT NULL,
    cl double precision DEFAULT 0.0 NOT NULL,
    dl double precision DEFAULT 0.0 NOT NULL,
    tot double precision DEFAULT 0.0 NOT NULL,
    bot double precision DEFAULT 0.0 NOT NULL,
    dip double precision DEFAULT 0.0 NOT NULL,
    fault double precision DEFAULT 0.0 NOT NULL
);


ALTER TABLE public.projections OWNER TO umsdata;

--
-- Name: projections_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE projections_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.projections_id_seq OWNER TO umsdata;

--
-- Name: projections_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE projections_id_seq OWNED BY projections.id;


--
-- Name: splotlist; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE splotlist (
    id integer NOT NULL,
    ptype text DEFAULT ''::text NOT NULL,
    mtype text DEFAULT ''::text NOT NULL,
    inputa double precision DEFAULT 5.0 NOT NULL,
    inputb double precision DEFAULT 6.0 NOT NULL,
    mintvd double precision DEFAULT 99999.0 NOT NULL,
    maxtvd double precision DEFAULT (-99999.0) NOT NULL,
    minvs double precision DEFAULT 99999.0 NOT NULL,
    maxvs double precision DEFAULT (-99999.0) NOT NULL
);


ALTER TABLE public.splotlist OWNER TO umsdata;

--
-- Name: splotlist_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE splotlist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.splotlist_id_seq OWNER TO umsdata;

--
-- Name: splotlist_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE splotlist_id_seq OWNED BY splotlist.id;


--
-- Name: surveys; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE surveys (
    azm numeric,
    dl numeric,
    ew numeric,
    id integer NOT NULL,
    inc numeric,
    md numeric,
    ns numeric,
    temp numeric,
    tvd numeric,
    vs numeric,
    ca numeric,
    cd numeric,
    hide integer DEFAULT 0 NOT NULL,
    plan integer DEFAULT 0 NOT NULL,
    cl double precision DEFAULT 0 NOT NULL,
    tot double precision DEFAULT 0 NOT NULL,
    bot double precision DEFAULT 0 NOT NULL,
    dip double precision DEFAULT 0 NOT NULL,
    fault double precision DEFAULT 0 NOT NULL
);


ALTER TABLE public.surveys OWNER TO umsdata;

--
-- Name: surveys_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE surveys_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.surveys_id_seq OWNER TO umsdata;

--
-- Name: surveys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE surveys_id_seq OWNED BY surveys.id;


--
-- Name: wblog; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE wblog (
    id integer NOT NULL,
    md numeric,
    tvd numeric,
    value numeric,
    vs numeric
);


ALTER TABLE public.wblog OWNER TO umsdata;

--
-- Name: wblog_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE wblog_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.wblog_id_seq OWNER TO umsdata;

--
-- Name: wblog_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE wblog_id_seq OWNED BY wblog.id;


--
-- Name: wellinfo; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE wellinfo (
    depthunits numeric,
    propazm numeric,
    operatorphone2 text,
    operatorphone1 text,
    operatoremail2 text,
    operatoremail1 text,
    directionalphone2 text,
    directionalphone1 text,
    directionalemail2 text,
    directionalemail1 text,
    field text,
    jobnumber text,
    description text,
    county text,
    stateprov text,
    country text,
    rigid text,
    wellid text,
    location text,
    wellborename text,
    directionalzip text,
    directionaladdress2 text,
    directionaladdress1 text,
    directionalname text,
    directionalcontact2 text,
    directionalcontact1 text,
    operatorcontact2 text,
    operatorcontact1 text,
    id integer NOT NULL,
    operatorname text,
    tot double precision DEFAULT 0 NOT NULL,
    bot double precision DEFAULT 0 NOT NULL,
    projection double precision DEFAULT 0 NOT NULL,
    bitoffset double precision DEFAULT 0 NOT NULL,
    projdip double precision DEFAULT 0.0 NOT NULL,
    pbhl_easting double precision DEFAULT 0.0 NOT NULL,
    pbhl_northing double precision DEFAULT 0.0 NOT NULL,
    survey_easting double precision DEFAULT 0.0 NOT NULL,
    survey_northing double precision DEFAULT 0.0 NOT NULL,
    landing_easting double precision DEFAULT 0.0 NOT NULL,
    landing_northing double precision DEFAULT 0.0 NOT NULL,
    elev_ground double precision DEFAULT 0.0 NOT NULL,
    elev_rkb double precision DEFAULT 0.0 NOT NULL,
    correction text DEFAULT 'True North'::text NOT NULL,
    coordsys text DEFAULT 'Polar'::text NOT NULL,
    padata text DEFAULT '0,0,0'::text NOT NULL,
    pbdata text DEFAULT '0,0,0'::text NOT NULL,
    pamethod integer DEFAULT 0 NOT NULL,
    pbmethod integer DEFAULT 0 NOT NULL,
    startdate date,
    enddate date,
    colorwp text,
    colortot text DEFAULT 'ff0000'::text NOT NULL,
    colorbot text DEFAULT 'ff0000'::text NOT NULL
);


ALTER TABLE public.wellinfo OWNER TO umsdata;

--
-- Name: wellinfo_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE wellinfo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.wellinfo_id_seq OWNER TO umsdata;

--
-- Name: wellinfo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE wellinfo_id_seq OWNED BY wellinfo.id;


--
-- Name: welllogs; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE welllogs (
    color text,
    dip numeric,
    endmd numeric,
    endtvd numeric,
    endvs numeric,
    fault numeric,
    filter numeric,
    id integer NOT NULL,
    scaleleft numeric,
    scaleright numeric,
    startmd numeric,
    starttvd numeric,
    startvs numeric,
    tablename text,
    realname text,
    startdepth double precision DEFAULT 0 NOT NULL,
    enddepth double precision DEFAULT 0 NOT NULL,
    scalebias numeric DEFAULT 0 NOT NULL,
    scalefactor numeric DEFAULT 1 NOT NULL,
    tot double precision DEFAULT 0 NOT NULL,
    bot double precision DEFAULT 0 NOT NULL,
    hide integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.welllogs OWNER TO umsdata;

--
-- Name: welllogs_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE welllogs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.welllogs_id_seq OWNER TO umsdata;

--
-- Name: welllogs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE welllogs_id_seq OWNED BY welllogs.id;


--
-- Name: wellplan; Type: TABLE; Schema: public; Owner: umsdata; Tablespace: 
--

CREATE TABLE wellplan (
    azm numeric,
    dl numeric,
    ew numeric,
    id integer NOT NULL,
    inc numeric,
    md numeric,
    ns numeric,
    temp numeric,
    tvd numeric,
    vs numeric,
    ca numeric,
    cd numeric,
    hide integer DEFAULT 0 NOT NULL,
    plan integer DEFAULT 0 NOT NULL,
    cl double precision DEFAULT 0 NOT NULL
);


ALTER TABLE public.wellplan OWNER TO umsdata;

--
-- Name: wellplan_id_seq; Type: SEQUENCE; Schema: public; Owner: umsdata
--

CREATE SEQUENCE wellplan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.wellplan_id_seq OWNER TO umsdata;

--
-- Name: wellplan_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: umsdata
--

ALTER SEQUENCE wellplan_id_seq OWNED BY wellplan.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE "CLD-9" ALTER COLUMN id SET DEFAULT nextval('"CLD-9_id_seq"'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE addforms ALTER COLUMN id SET DEFAULT nextval('addforms_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE addformsdata ALTER COLUMN id SET DEFAULT nextval('addformsdata_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE appinfo ALTER COLUMN id SET DEFAULT nextval('appinfo_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE controllogs ALTER COLUMN id SET DEFAULT nextval('controllogs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE edatalogs ALTER COLUMN id SET DEFAULT nextval('edatalogs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE emailinfo ALTER COLUMN id SET DEFAULT nextval('emailinfo_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE emaillist ALTER COLUMN id SET DEFAULT nextval('emaillist_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE projections ALTER COLUMN id SET DEFAULT nextval('projections_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE splotlist ALTER COLUMN id SET DEFAULT nextval('splotlist_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE surveys ALTER COLUMN id SET DEFAULT nextval('surveys_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE wblog ALTER COLUMN id SET DEFAULT nextval('wblog_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE wellinfo ALTER COLUMN id SET DEFAULT nextval('wellinfo_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE welllogs ALTER COLUMN id SET DEFAULT nextval('welllogs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: umsdata
--

ALTER TABLE wellplan ALTER COLUMN id SET DEFAULT nextval('wellplan_id_seq'::regclass);


--
-- Name: CLD-9_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY "CLD-9"
    ADD CONSTRAINT "CLD-9_pkey" PRIMARY KEY (id);


--
-- Name: addforms_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY addforms
    ADD CONSTRAINT addforms_pkey PRIMARY KEY (id);


--
-- Name: addformsdata_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY addformsdata
    ADD CONSTRAINT addformsdata_pkey PRIMARY KEY (id);


--
-- Name: appinfo_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY appinfo
    ADD CONSTRAINT appinfo_pkey PRIMARY KEY (id);


--
-- Name: controllogs_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY controllogs
    ADD CONSTRAINT controllogs_pkey PRIMARY KEY (id);


--
-- Name: edatalogs_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY edatalogs
    ADD CONSTRAINT edatalogs_pkey PRIMARY KEY (id);


--
-- Name: emailinfo_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY emailinfo
    ADD CONSTRAINT emailinfo_pkey PRIMARY KEY (id);


--
-- Name: emaillist_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY emaillist
    ADD CONSTRAINT emaillist_pkey PRIMARY KEY (id);


--
-- Name: id; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY welllogs
    ADD CONSTRAINT id PRIMARY KEY (id);


--
-- Name: projections_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY projections
    ADD CONSTRAINT projections_pkey PRIMARY KEY (id);


--
-- Name: splotlist_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY splotlist
    ADD CONSTRAINT splotlist_pkey PRIMARY KEY (id);


--
-- Name: surveys_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY surveys
    ADD CONSTRAINT surveys_pkey PRIMARY KEY (id);


--
-- Name: wblog_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY wblog
    ADD CONSTRAINT wblog_pkey PRIMARY KEY (id);


--
-- Name: wellinfo_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY wellinfo
    ADD CONSTRAINT wellinfo_pkey PRIMARY KEY (id);


--
-- Name: wellplan_pkey; Type: CONSTRAINT; Schema: public; Owner: umsdata; Tablespace: 
--

ALTER TABLE ONLY wellplan
    ADD CONSTRAINT wellplan_pkey PRIMARY KEY (id);


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

