/*==============================================================*/
/* Nom de SGBD :  ORACLE Version 10g                            */
/* Date de création :  24/03/2026                               */
/*==============================================================*/


alter table ENTREPRISE
   drop constraint FK_ENTREPRI_POSSEDER2_UTILISAT;

alter table IMPACT_ENVIROMMENTAL
   drop constraint FK_IMPACT_E_GENERER_MATERIAU;

alter table MATERIAU
   drop constraint FK_MATERIAU_CLASSER_CATEGORI;

alter table MATERIAU
   drop constraint FK_MATERIAU_GENERER2_IMPACT_E;

alter table MATERIAU
   drop constraint FK_MATERIAU_PUBLIER_UTILISAT;

alter table MESSAGE
   drop constraint FK_MESSAGE_ENVOYER_UTILISAT;

alter table MESSAGE
   drop constraint FK_MESSAGE_RECEVOIR_UTILISAT;

alter table RESERVATION
   drop constraint FK_RESERVAT_CONCERNER_MATERIAU;

alter table RESERVATION
   drop constraint FK_RESERVAT_EFFECTUER_UTILISAT;

alter table SIGNALEMENT
   drop constraint FK_SIGNALEM_EST_SIGNA_MATERIAU;

alter table SIGNALEMENT
   drop constraint FK_SIGNALEM_SIGNALER_UTILISAT;

alter table UTILISATEUR
   drop constraint FK_UTILISAT_POSSEDER_ENTREPRI;

drop table CATEGORIE cascade constraints;

drop index POSSEDER2_FK;

drop table ENTREPRISE cascade constraints;

drop index GENERER_FK;

drop table IMPACT_ENVIROMMENTAL cascade constraints;

drop index GENERER2_FK;

drop index CLASSER_FK;

drop index PUBLIER_FK;

drop table MATERIAU cascade constraints;

drop index RECEVOIR_FK;

drop index ENVOYER_FK;

drop table MESSAGE cascade constraints;

drop index CONCERNER_FK;

drop index EFFECTUER_FK;

drop table RESERVATION cascade constraints;

drop index EST_SIGNALE_FK;

drop index SIGNALER_FK;

drop table SIGNALEMENT cascade constraints;

drop index POSSEDER_FK;

drop table UTILISATEUR cascade constraints;

create table CATEGORIE  (
   ID_CATEGORIE         NUMBER(10)                      not null,
   NOM_CATEGORIE        VARCHAR2(100)                   not null,
   constraint PK_CATEGORIE primary key (ID_CATEGORIE)
);

create table UTILISATEUR  (
   ID_USER              NUMBER(10)                      not null,
   ID_ENTREP            NUMBER(10),
   NOM_USER             VARCHAR2(100)                   not null,
   PRENOM_USER          VARCHAR2(100)                   not null,
   EMAIL_USER           VARCHAR2(100)                   not null,
   TELEPHONE_USER       VARCHAR2(15)                    not null,
   MOT_DE_PASSE_USER    VARCHAR2(255)                   not null,
   TYPE_USER            VARCHAR2(30)                    not null,
   ADRESSE_USER         VARCHAR2(255)                   not null,
   VILLE_USER           VARCHAR2(255)                   not null,
   DATE_INSCRIPTION_USER DATE                           not null,
   constraint PK_UTILISATEUR primary key (ID_USER),
   constraint UQ_EMAIL_USER unique (EMAIL_USER),
   constraint UQ_TEL_USER unique (TELEPHONE_USER),
   constraint CK_TYPE_USER check (TYPE_USER in ('entreprise', 'artisan', 'particulier', 'admin'))
);

create index POSSEDER_FK on UTILISATEUR (
   ID_ENTREP ASC
);

create table ENTREPRISE  (
   ID_ENTREP            NUMBER(10)                      not null,
   ID_USER              NUMBER(10)                      not null,
   NOM_ENTREP           VARCHAR2(150)                   not null,
   SECTEUR_ACTIVITE_ENTREP VARCHAR2(100),
   MATRICULE_FISCALE_ENTREP VARCHAR2(15)                not null,
   constraint PK_ENTREPRISE primary key (ID_ENTREP),
   constraint UQ_MATRICULE_ENTREP unique (MATRICULE_FISCALE_ENTREP)
);

create index POSSEDER2_FK on ENTREPRISE (
   ID_USER ASC
);

create table IMPACT_ENVIROMMENTAL  (
   ID_IMPACT            NUMBER(10)                      not null,
   ID_MATERIAU          NUMBER(10),
   DECHETS_EVITE_KG_IMPACT NUMBER(10,2)                 not null,
   CO2_ECONOMISE_KG_IMPACT NUMBER(10,2)                 not null,
   DATE_IMPACT          TIMESTAMP                       not null,
   constraint PK_IMPACT_ENVIROMMENTAL primary key (ID_IMPACT)
);

create index GENERER_FK on IMPACT_ENVIROMMENTAL (
   ID_MATERIAU ASC
);

create table MATERIAU  (
   ID_MATERIAU          NUMBER(10)                      not null,
   ID_CATEGORIE         NUMBER(10)                      not null,
   ID_USER              NUMBER(10)                      not null,
   ID_IMPACT            NUMBER(10)                      not null,
   NOM_MATERIAU         VARCHAR2(150)                   not null,
   DESCRIPTIN_MATERIAU  VARCHAR2(400),
   ETAT_MATERIAU        VARCHAR2(100)                   not null,
   QUANTITE_MATERIAU    NUMBER(3)                       not null,
   DIMENSIONS_MATERIAU  VARCHAR2(50),
   MODE_ECHANGE_MATERIAU VARCHAR2(50)                   not null,
   DISPONIBLITE_MATERIAU VARCHAR2(20)                   not null,
   DATE_PUBLICATION_MATERIAU DATE                       not null,
   PRIX_MATERIAU        NUMBER(10,2)                    not null,
   constraint PK_MATERIAU primary key (ID_MATERIAU),
   constraint CK_MODE_ECHANGE check (MODE_ECHANGE_MATERIAU in ('don', 'vente', 'troc'))
);

create index PUBLIER_FK on MATERIAU (
   ID_USER ASC
);

create index CLASSER_FK on MATERIAU (
   ID_CATEGORIE ASC
);

create index GENERER2_FK on MATERIAU (
   ID_IMPACT ASC
);

create table MESSAGE  (
   ID_MESSAGE           NUMBER(10)                      not null,
   ID_USER              NUMBER(10)                      not null,
   UTI_ID_USER          NUMBER(10)                      not null,
   CONTENU_MESSAGE      VARCHAR2(400)                   not null,
   DATE_ENVOI_MESSAGE   TIMESTAMP                       not null,
   constraint PK_MESSAGE primary key (ID_MESSAGE)
);

create index ENVOYER_FK on MESSAGE (
   UTI_ID_USER ASC
);

create index RECEVOIR_FK on MESSAGE (
   ID_USER ASC
);

create table RESERVATION  (
   ID_RESERVATION       NUMBER(10)                      not null,
   ID_MATERIAU          NUMBER(10)                      not null,
   ID_USER              NUMBER(10)                      not null,
   DATE_RESERVATION     DATE                            not null,
   STATUT_RESERVATION   VARCHAR2(20)                    not null,
   constraint PK_RESERVATION primary key (ID_RESERVATION),
   constraint CK_STATUT_RESERVATION check (STATUT_RESERVATION in ('en attente', 'confirmee', 'annulee'))
);

create index EFFECTUER_FK on RESERVATION (
   ID_USER ASC
);

create index CONCERNER_FK on RESERVATION (
   ID_MATERIAU ASC
);

create table SIGNALEMENT  (
   ID_SIGNALEMENT       NUMBER(10)                      not null,
   ID_MATERIAU          NUMBER(10)                      not null,
   ID_USER              NUMBER(10)                      not null,
   MOTIF_SIGNALEMENT    VARCHAR2(300)                   not null,
   DATE_SINGALEMENT     DATE                            not null,
   constraint PK_SIGNALEMENT primary key (ID_SIGNALEMENT)
);

create index SIGNALER_FK on SIGNALEMENT (
   ID_USER ASC
);

create index EST_SIGNALE_FK on SIGNALEMENT (
   ID_MATERIAU ASC
);


alter table ENTREPRISE
   add constraint FK_ENTREPRI_POSSEDER2_UTILISAT foreign key (ID_USER)
      references UTILISATEUR (ID_USER);

alter table IMPACT_ENVIROMMENTAL
   add constraint FK_IMPACT_E_GENERER_MATERIAU foreign key (ID_MATERIAU)
      references MATERIAU (ID_MATERIAU);

alter table MATERIAU
   add constraint FK_MATERIAU_CLASSER_CATEGORI foreign key (ID_CATEGORIE)
      references CATEGORIE (ID_CATEGORIE);

alter table MATERIAU
   add constraint FK_MATERIAU_GENERER2_IMPACT_E foreign key (ID_IMPACT)
      references IMPACT_ENVIROMMENTAL (ID_IMPACT);

alter table MATERIAU
   add constraint FK_MATERIAU_PUBLIER_UTILISAT foreign key (ID_USER)
      references UTILISATEUR (ID_USER);

alter table MESSAGE
   add constraint FK_MESSAGE_ENVOYER_UTILISAT foreign key (UTI_ID_USER)
      references UTILISATEUR (ID_USER);

alter table MESSAGE
   add constraint FK_MESSAGE_RECEVOIR_UTILISAT foreign key (ID_USER)
      references UTILISATEUR (ID_USER);

alter table RESERVATION
   add constraint FK_RESERVAT_CONCERNER_MATERIAU foreign key (ID_MATERIAU)
      references MATERIAU (ID_MATERIAU);

alter table RESERVATION
   add constraint FK_RESERVAT_EFFECTUER_UTILISAT foreign key (ID_USER)
      references UTILISATEUR (ID_USER);

alter table SIGNALEMENT
   add constraint FK_SIGNALEM_EST_SIGNA_MATERIAU foreign key (ID_MATERIAU)
      references MATERIAU (ID_MATERIAU);

alter table SIGNALEMENT
   add constraint FK_SIGNALEM_SIGNALER_UTILISAT foreign key (ID_USER)
      references UTILISATEUR (ID_USER);

alter table UTILISATEUR
   add constraint FK_UTILISAT_POSSEDER_ENTREPRI foreign key (ID_ENTREP)
      references ENTREPRISE (ID_ENTREP);