CREATE TABLE RECEBIMENTO_DESCARGA
(
  COD_RECEBIMENTO_DESCARGA NUMBER (8) NOT NULL ,
  COD_RECEBIMENTO          VARCHAR2 (20 BYTE) NOT NULL ,
  COD_USUARIO              NUMBER (8) NOT NULL ,
  DTH_VINCULO              DATE
);

ALTER TABLE RECEBIMENTO_DESCARGA ADD CONSTRAINT RECEB_DESC_PK PRIMARY KEY ( COD_RECEBIMENTO_DESCARGA ) ;
ALTER TABLE RECEBIMENTO_DESCARGA ADD CONSTRAINT RECEB_CONF_FK FOREIGN KEY ( COD_RECEBIMENTO ) REFERENCES RECEBIMENTO ( COD_RECEBIMENTO ) NOT DEFERRABLE ;
ALTER TABLE RECEBIMENTO_DESCARGA ADD CONSTRAINT RECEB_CON_USU_FK FOREIGN KEY ( COD_USUARIO ) REFERENCES USUARIO ( COD_USUARIO ) NOT DEFERRABLE ;

CREATE SEQUENCE SQ_RECE_DESC_01
START WITH 1
MAXVALUE 99999999999999999
MINVALUE 1
NOCYCLE
NOCACHE
NOORDER;

INSERT INTO "PERFIL_USUARIO" (COD_PERFIL_USUARIO, DSC_PERFIL_USUARIO, NOM_PERFIL_USUARIO) VALUES (SQ_PERFIL_USUARIO_01.NEXTVAL, 'Descarregador Recebimento', 'DESCARREGADOR RECEBI');

INSERT INTO RECURSO (COD_RECURSO, DSC_RECURSO, COD_RECURSO_PAI, NOM_RECURSO) VALUES (SQ_RECURSO_01.NEXTVAL, 'Descarga', 0, 'produtividade:descarga');
INSERT INTO RECURSO_ACAO (COD_RECURSO_ACAO, COD_RECURSO, COD_ACAO, DSC_RECURSO_ACAO)VALUES (SQ_RECURSO_ACAO_01.NEXTVAL, (SELECT COD_RECURSO FROM RECURSO WHERE NOM_RECURSO = 'produtividade:descarga'), (SELECT COD_ACAO FROM ACAO WHERE NOM_ACAO = 'index'), 'Início Descarga');
INSERT INTO MENU_ITEM (COD_MENU_ITEM, COD_RECURSO_ACAO, COD_PAI, DSC_MENU_ITEM, NUM_PESO, DSC_URL, SHOW) VALUES (SQ_MENU_ITEM_01.NEXTVAL, (SELECT COD_RECURSO_ACAO FROM RECURSO_ACAO WHERE COD_RECURSO = (SELECT COD_RECURSO FROM RECURSO WHERE NOM_RECURSO = 'produtividade:descarga') AND COD_ACAO = '5'), 49, 'Descarga Recebimento', 1, '#', 'N');

INSERT INTO PARAMETRO (COD_PARAMETRO, COD_CONTEXTO_PARAMETRO, DSC_PARAMETRO, DSC_TITULO_PARAMETRO, IND_PARAMETRO_SISTEMA, COD_TIPO_ATRIBUTO, DSC_VALOR_PARAMETRO) VALUES (SQ_PARAMETRO_01.NEXTVAL, (SELECT COD_CONTEXTO_PARAMETRO FROM CONTEXTO_PARAMETRO WHERE DSC_CONTEXTO_PARAMETRO = 'RECEBIMENTO'), 'DESCARGA_RECEBIMENTO', 'Descarga recebimento obrigatorio', 'N', 'A', 'S');

INSERT INTO RECURSO (COD_RECURSO, COD_RECURSO_PAI, DSC_RECURSO, NOM_RECURSO) VALUES (SQ_RECURSO_01.NEXTVAL, 0 ,'Relatório Descarga Recebimento', 'produtividade:relatorio_descarga');
INSERT INTO RECURSO_ACAO (COD_RECURSO_ACAO, COD_RECURSO, COD_ACAO, DSC_RECURSO_ACAO) VALUES (SQ_RECURSO_ACAO_01.NEXTVAL, (SELECT COD_RECURSO FROM RECURSO WHERE NOM_RECURSO = 'produtividade:relatorio_descarga'), (SELECT COD_ACAO FROM ACAO WHERE NOM_ACAO = 'index'), 'Início');

INSERT INTO MENU_ITEM (COD_MENU_ITEM, COD_RECURSO_ACAO, COD_PAI, DSC_MENU_ITEM, NUM_PESO, DSC_URL, DSC_TARGET, SHOW) VALUES (SQ_MENU_ITEM_01.NEXTVAL,
        (SELECT COD_RECURSO_ACAO FROM RECURSO_ACAO WHERE COD_RECURSO = (SELECT COD_RECURSO FROM RECURSO WHERE NOM_RECURSO = 'produtividade:relatorio_descarga')),
        (SELECT COD_MENU_ITEM FROM MENU_ITEM WHERE DSC_MENU_ITEM = 'Recebimento' and cod_pai <> 0 ),
        'Descarga Recebimento',
        10, '#', '_self', 'S' );

CREATE OR REPLACE FORCE VIEW "WMS_ADM"."V_PESO_RECEBIMENTO" ("COD_RECEBIMENTO", "COD_PRODUTO", "DSC_GRADE", "PESO", "CUBAGEM") AS
  SELECT RC.COD_RECEBIMENTO,
					RC.COD_PRODUTO,
          RC.DSC_GRADE,
          NVL(SUM (RE.NUM_PESO),SUM (RV.NUM_PESO)) * RC.QTD AS PESO,
				  NVL(SUM (RE.NUM_CUBAGEM),SUM (RV.NUM_CUBAGEM)) * RC.QTD AS CUBAGEM
     FROM V_QTD_RECEBIMENTO RC
LEFT JOIN (SELECT DISTINCT RE.COD_RECEBIMENTO,
                  PE.COD_PRODUTO,
                  PE.DSC_GRADE,
                  RE.COD_OS,
                  RE.COD_NORMA_PALETIZACAO,
                  PDL.NUM_PESO,
                  PDL.NUM_CUBAGEM,
                  PE.COD_PRODUTO_EMBALAGEM
             FROM RECEBIMENTO_EMBALAGEM RE
       INNER JOIN PRODUTO_EMBALAGEM PE ON PE.COD_PRODUTO_EMBALAGEM = RE.COD_PRODUTO_EMBALAGEM
        LEFT JOIN PRODUTO_DADO_LOGISTICO PDL ON PE.COD_PRODUTO_EMBALAGEM = PDL.COD_PRODUTO_EMBALAGEM) RE
               ON RE.COD_PRODUTO = RC.COD_PRODUTO
              AND RE.DSC_GRADE = RC.DSC_GRADE
              AND RE.COD_RECEBIMENTO = RC.COD_RECEBIMENTO
              AND RE.COD_OS = RC.COD_OS
LEFT JOIN (SELECT DISTINCT RV.COD_RECEBIMENTO,
                  PV.COD_PRODUTO,
                  PV.DSC_GRADE,
                  RV.COD_OS,
                  RV.COD_NORMA_PALETIZACAO,
                  PV.NUM_PESO,
                  PV.NUM_CUBAGEM,
                  PV.COD_PRODUTO_VOLUME
             FROM RECEBIMENTO_VOLUME RV
            INNER JOIN PRODUTO_VOLUME PV ON PV.COD_PRODUTO_VOLUME = RV.COD_PRODUTO_VOLUME) RV
               ON RV.COD_PRODUTO = RC.COD_PRODUTO
              AND RV.DSC_GRADE = RC.DSC_GRADE
              AND RV.COD_RECEBIMENTO = RC.COD_RECEBIMENTO
              AND RV.COD_OS = RC.COD_OS
    GROUP BY RC.COD_RECEBIMENTO, RC.COD_PRODUTO, RC.DSC_GRADE, RC.QTD;