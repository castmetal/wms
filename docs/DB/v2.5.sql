﻿INSERT INTO "TIPO_SIGLA" (COD_TIPO_SIGLA, DSC_TIPO_SIGLA, IND_SIGLA_SISTEMA) VALUES (72, 'STATUS ETIQUETA SEPARACAO', 'S');

INSERT INTO "SIGLA" (COD_SIGLA, COD_TIPO_SIGLA, DSC_SIGLA, COD_REFERENCIA_SIGLA) VALUES (522, (SELECT COD_TIPO_SIGLA FROM TIPO_SIGLA WHERE DSC_TIPO_SIGLA = 'STATUS ETIQUETA SEPARACAO'), 'PENDENTE DE IMPRESSÃO', 'PI');
INSERT INTO "SIGLA" (COD_SIGLA, COD_TIPO_SIGLA, DSC_SIGLA, COD_REFERENCIA_SIGLA) VALUES (523, (SELECT COD_TIPO_SIGLA FROM TIPO_SIGLA WHERE DSC_TIPO_SIGLA = 'STATUS ETIQUETA SEPARACAO'), 'ETIQUETA GERADA', 'EG');
INSERT INTO "SIGLA" (COD_SIGLA, COD_TIPO_SIGLA, DSC_SIGLA, COD_REFERENCIA_SIGLA) VALUES (524, (SELECT COD_TIPO_SIGLA FROM TIPO_SIGLA WHERE DSC_TIPO_SIGLA = 'STATUS ETIQUETA SEPARACAO'), 'PENDENTE DE CORTE', 'PC');
INSERT INTO "SIGLA" (COD_SIGLA, COD_TIPO_SIGLA, DSC_SIGLA, COD_REFERENCIA_SIGLA) VALUES (525, (SELECT COD_TIPO_SIGLA FROM TIPO_SIGLA WHERE DSC_TIPO_SIGLA = 'STATUS ETIQUETA SEPARACAO'), 'CORTADO', 'CR');
INSERT INTO "SIGLA" (COD_SIGLA, COD_TIPO_SIGLA, DSC_SIGLA, COD_REFERENCIA_SIGLA) VALUES (526, (SELECT COD_TIPO_SIGLA FROM TIPO_SIGLA WHERE DSC_TIPO_SIGLA = 'STATUS ETIQUETA SEPARACAO'), 'CONFERIDO', 'CN');

INSERT INTO "SIGLA" (COD_SIGLA, COD_TIPO_SIGLA, DSC_SIGLA, COD_REFERENCIA_SIGLA) VALUES (527, (SELECT COD_TIPO_SIGLA FROM TIPO_SIGLA WHERE DSC_TIPO_SIGLA = 'TIPO DE PEDIDO'), 'SUGESTAO', 'S');
INSERT INTO "SIGLA" (COD_SIGLA, COD_TIPO_SIGLA, DSC_SIGLA, COD_REFERENCIA_SIGLA) VALUES (528, (SELECT COD_TIPO_SIGLA FROM TIPO_SIGLA WHERE DSC_TIPO_SIGLA = 'TIPO DE PEDIDO'), 'AVULSO', 'AV');
INSERT INTO "SIGLA" (COD_SIGLA, COD_TIPO_SIGLA, DSC_SIGLA, COD_REFERENCIA_SIGLA) VALUES (529, (SELECT COD_TIPO_SIGLA FROM TIPO_SIGLA WHERE DSC_TIPO_SIGLA = 'TIPO DE PEDIDO'), 'ASSISTENCIA', 'A');

INSERT INTO RECURSO (COD_RECURSO, DSC_RECURSO, COD_RECURSO_PAI, NOM_RECURSO) VALUES (SQ_RECURSO_01.NEXTVAL, 'Relatorio Produtos Expedicao', 0, 'expedicao:relatorio_produtos-expedicao');

INSERT INTO RECURSO_ACAO (COD_RECURSO_ACAO, COD_RECURSO, COD_ACAO, DSC_RECURSO_ACAO) VALUES (SQ_RECURSO_ACAO_01.NEXTVAL, (SELECT COD_RECURSO FROM RECURSO WHERE NOM_RECURSO = 'expedicao:relatorio_produtos-expedicao'), (SELECT COD_ACAO FROM ACAO WHERE NOM_ACAO = 'index'), 'index relatorio produtos expedicao');

INSERT INTO "MENU_ITEM" (COD_MENU_ITEM, COD_RECURSO_ACAO, COD_PAI, DSC_MENU_ITEM, NUM_PESO, DSC_URL, SHOW) VALUES (SQ_MENU_ITEM_01.NEXTVAL, (SELECT COD_RECURSO_ACAO FROM RECURSO_ACAO RA, RECURSO R WHERE RA.COD_RECURSO = R.COD_RECURSO AND NOM_RECURSO = 'expedicao:relatorio_produtos-expedicao'), (SELECT COD_MENU_ITEM FROM MENU_ITEM WHERE DSC_MENU_ITEM = 'Expedição Mercadorias'), 'Relatorio Produtos Expedicao', '0', '#', 'N');

INSERT INTO RECURSO (COD_RECURSO, DSC_RECURSO, COD_RECURSO_PAI, NOM_RECURSO) VALUES (SQ_RECURSO_01.NEXTVAL, 'Conferencia', 0, 'expedicao:conferencia');

INSERT INTO RECURSO_ACAO (COD_RECURSO_ACAO, COD_RECURSO, COD_ACAO, DSC_RECURSO_ACAO) VALUES (SQ_RECURSO_ACAO_01.NEXTVAL, (SELECT COD_RECURSO FROM RECURSO WHERE NOM_RECURSO = 'expedicao:conferencia'), (SELECT COD_ACAO FROM ACAO WHERE NOM_ACAO = 'finalizar'), 'finalizar expedicao');
INSERT INTO RECURSO_ACAO (COD_RECURSO_ACAO, COD_RECURSO, COD_ACAO, DSC_RECURSO_ACAO) VALUES (SQ_RECURSO_ACAO_01.NEXTVAL, (SELECT COD_RECURSO FROM RECURSO WHERE NOM_RECURSO = 'expedicao:conferencia'), (SELECT COD_ACAO FROM ACAO WHERE NOM_ACAO = 'index'), 'index conferencia');

INSERT INTO "MENU_ITEM" (COD_MENU_ITEM, COD_RECURSO_ACAO, COD_PAI, DSC_MENU_ITEM, NUM_PESO, DSC_URL, SHOW) VALUES (SQ_MENU_ITEM_01.NEXTVAL, (SELECT MIN(COD_RECURSO_ACAO) FROM RECURSO_ACAO RA, RECURSO R WHERE RA.COD_RECURSO = R.COD_RECURSO AND NOM_RECURSO = 'expedicao:conferencia'), (SELECT COD_MENU_ITEM FROM MENU_ITEM WHERE DSC_MENU_ITEM = 'Expedição Mercadorias'), 'Conferencia expedicao', '0', '#', 'N');


  CREATE OR REPLACE FORCE VIEW "WMS_ADM"."V_REL_PRODUTOS_EXPEDICAO" ("COD_EXPEDICAO", "COD_CARGA", "COD_CARGA_EXTERNO", "DSC_PLACA_EXPEDICAO", "LINHA_ENTREGA", "COD_ITINERARIO", "DSC_ITINERARIO", "PRODUTO", "DESCRICAO", "MAPA", "GRADE", "QUANTIDADE", "FABRICANTE", "NUM_PESO", "NUM_LARGURA", "NUM_ALTURA", "NUM_PROFUNDIDADE", "DSC_VOLUME", "IND_PADRAO", "CENTRAL_ENTREGA", "SEQ_QUEBRA") AS
  SELECT BASE.COD_EXPEDICAO,
       BASE.COD_CARGA,
       BASE.COD_CARGA_EXTERNO,
       BASE.DSC_PLACA_EXPEDICAO,
       BASE.LINHA_ENTREGA,
       BASE.COD_ITINERARIO,
       BASE.DSC_ITINERARIO,
       BASE.PRODUTO,
       BASE.DESCRICAO,
       BASE.MAPA,
       BASE.GRADE,
       BASE.QUANTIDADE - BASE.QTD_CORTE AS QUANTIDADE,
       BASE.FABRICANTE,
       BASE.NUM_PESO,
       BASE.NUM_LARGURA,
       BASE.NUM_ALTURA,
       BASE.NUM_PROFUNDIDADE,
       BASE.DSC_VOLUME,
       BASE.IND_PADRAO,
       BASE.CENTRAL_ENTREGA,
       BASE.SEQ_QUEBRA
  FROM
    (
            SELECT C.COD_EXPEDICAO,
               C.COD_CARGA,
               C.DSC_PLACA_EXPEDICAO,
               C.COD_CARGA_EXTERNO,
               P.DSC_LINHA_ENTREGA LINHA_ENTREGA,
               I.DSC_ITINERARIO,
               I.COD_ITINERARIO,
               PP.COD_PRODUTO PRODUTO,
               PROD.DSC_PRODUTO DESCRICAO,
               LS.DSC_LINHA_SEPARACAO MAPA,
               PP.DSC_GRADE GRADE,
               SUM(PP.QUANTIDADE) QUANTIDADE,
               F.NOM_FABRICANTE FABRICANTE,
               PDL.NUM_PESO,
               PDL.NUM_LARGURA,
               PDL.NUM_ALTURA,
               PDL.NUM_PROFUNDIDADE,
               PE.DSC_EMBALAGEM DSC_VOLUME,
               PE.IND_PADRAO,
               P.CENTRAL_ENTREGA,
               0 AS QTD_CORTE,
               CASE WHEN LS.COD_LINHA_SEPARACAO = 13 THEN 0
                    WHEN LS.COD_LINHA_SEPARACAO = 15 THEN 0
                    ELSE 1
               END AS SEQ_QUEBRA
          FROM CARGA C
         INNER JOIN PEDIDO P
            ON P.COD_CARGA = C.COD_CARGA
         INNER JOIN ITINERARIO I
            ON P.COD_ITINERARIO = I.COD_ITINERARIO
         INNER JOIN PEDIDO_PRODUTO PP
            ON PP.COD_PEDIDO = P.COD_PEDIDO
         INNER JOIN PRODUTO PROD
            ON PP.COD_PRODUTO = PROD.COD_PRODUTO
           AND PP.DSC_GRADE  = PROD.DSC_GRADE
          LEFT JOIN FABRICANTE F
            ON PROD.COD_FABRICANTE = F.COD_FABRICANTE
          LEFT JOIN LINHA_SEPARACAO LS
            ON PROD.COD_LINHA_SEPARACAO = LS.COD_LINHA_SEPARACAO
          LEFT JOIN PRODUTO_EMBALAGEM PE
            ON PE.COD_PRODUTO = PROD.COD_PRODUTO
           AND PE.DSC_GRADE  = PROD.DSC_GRADE
          LEFT JOIN PRODUTO_DADO_LOGISTICO PDL
            ON PDL.COD_PRODUTO_EMBALAGEM = PE.COD_PRODUTO_EMBALAGEM
         WHERE NOT EXISTS (SELECT DISTINCT COD_PRODUTO,
                                  DSC_GRADE
                             FROM PRODUTO_VOLUME PV2
                            WHERE PV2.COD_PRODUTO = PP.COD_PRODUTO
                              AND PV2.DSC_GRADE   = PP.DSC_GRADE)
           AND NOT EXISTS (SELECT DISTINCT COD_PRODUTO,
                                  DSC_GRADE
                             FROM PRODUTO_EMBALAGEM PE2
                            WHERE PE2.COD_PRODUTO = PP.COD_PRODUTO
                              AND PE2.DSC_GRADE   = PP.DSC_GRADE)
         GROUP BY
               C.COD_EXPEDICAO,
               C.COD_CARGA,
               C.DSC_PLACA_EXPEDICAO,
               C.COD_CARGA_EXTERNO,
               P.DSC_LINHA_ENTREGA,
               I.DSC_ITINERARIO,
               I.COD_ITINERARIO,
               PP.COD_PRODUTO,
               PP.DSC_GRADE,
               LS.DSC_LINHA_SEPARACAO,
               PROD.DSC_PRODUTO,
               F.NOM_FABRICANTE,
               PDL.NUM_PESO,
               PDL.NUM_LARGURA,
               PDL.NUM_ALTURA,
               PDL.NUM_PROFUNDIDADE,
               PE.DSC_EMBALAGEM,
               PE.IND_PADRAO,
               P.CENTRAL_ENTREGA,
               LS.COD_LINHA_SEPARACAO
    UNION
        SELECT C.COD_EXPEDICAO,
               C.COD_CARGA,
               C.DSC_PLACA_EXPEDICAO,
               C.COD_CARGA_EXTERNO,
               P.DSC_LINHA_ENTREGA LINHA_ENTREGA,
               I.DSC_ITINERARIO,
               I.COD_ITINERARIO,
               PP.COD_PRODUTO PRODUTO,
               PROD.DSC_PRODUTO DESCRICAO,
               LS.DSC_LINHA_SEPARACAO MAPA,
               PP.DSC_GRADE GRADE,
               SUM(PP.QUANTIDADE) QUANTIDADE,
               F.NOM_FABRICANTE FABRICANTE,
               PV.NUM_PESO,
               PV.NUM_LARGURA,
               PV.NUM_ALTURA,
               PV.NUM_PROFUNDIDADE,
               PV.DSC_VOLUME,
               'S' AS IND_PADRAO,
               P.CENTRAL_ENTREGA,
               CASE WHEN CORTE.QTD_CORTE IS NULL THEN 0
                    ELSE CORTE.QTD_CORTE
               END AS QTD_CORTE,
               CASE WHEN LS.COD_LINHA_SEPARACAO = 13 THEN 0
                    WHEN LS.COD_LINHA_SEPARACAO = 15 THEN 0
                    ELSE 1
               END AS SEQ_QUEBRA
          FROM CARGA C
         INNER JOIN PEDIDO P
            ON P.COD_CARGA = C.COD_CARGA
         INNER JOIN ITINERARIO I
            ON P.COD_ITINERARIO = I.COD_ITINERARIO
         INNER JOIN PEDIDO_PRODUTO PP
            ON PP.COD_PEDIDO = P.COD_PEDIDO
         INNER JOIN PRODUTO PROD
            ON PP.COD_PRODUTO = PROD.COD_PRODUTO
           AND PP.DSC_GRADE  = PROD.DSC_GRADE
          LEFT JOIN FABRICANTE F
            ON PROD.COD_FABRICANTE = F.COD_FABRICANTE
          LEFT JOIN LINHA_SEPARACAO LS
            ON PROD.COD_LINHA_SEPARACAO = LS.COD_LINHA_SEPARACAO
         INNER JOIN PRODUTO_VOLUME PV
            ON PV.COD_PRODUTO = PROD.COD_PRODUTO
           AND PV.DSC_GRADE  = PROD.DSC_GRADE
          LEFT JOIN (SELECT COUNT(DISTINCT ES.COD_REFERENCIA) AS QTD_CORTE,
                            ES.DSC_GRADE,
                            ES.COD_PRODUTO,
                            ES.COD_PEDIDO
                       FROM ETIQUETA_SEPARACAO ES
                      WHERE ES.COD_STATUS IN (524,525)
                        AND ES.COD_PRODUTO_EMBALAGEM IS NULL
                        AND NOT ES.COD_REFERENCIA IS NULL
                      GROUP BY ES.DSC_GRADE, ES.COD_PRODUTO, ES.COD_PEDIDO) CORTE
            ON CORTE.COD_PRODUTO = PP.COD_PRODUTO
           AND CORTE.DSC_GRADE = PP.DSC_GRADE
           AND CORTE.COD_PEDIDO = PP.COD_PEDIDO
         GROUP BY
               C.COD_EXPEDICAO,
               I.DSC_ITINERARIO,
               I.COD_ITINERARIO,
               C.COD_CARGA,
               C.DSC_PLACA_EXPEDICAO,
               C.COD_CARGA_EXTERNO,
               P.DSC_LINHA_ENTREGA,
               PP.COD_PRODUTO,
               PP.DSC_GRADE,
               LS.DSC_LINHA_SEPARACAO,
               PROD.DSC_PRODUTO,
               F.NOM_FABRICANTE,
               PV.NUM_PESO,
               PV.NUM_LARGURA,
               PV.NUM_ALTURA,
               PV.NUM_PROFUNDIDADE,
               PV.DSC_VOLUME,
               P.CENTRAL_ENTREGA,
               CORTE.QTD_CORTE,
               LS.COD_LINHA_SEPARACAO
    UNION
        SELECT C.COD_EXPEDICAO,
               C.COD_CARGA,
               C.DSC_PLACA_EXPEDICAO,
               C.COD_CARGA_EXTERNO,
               P.DSC_LINHA_ENTREGA LINHA_ENTREGA,
               I.DSC_ITINERARIO,
               I.COD_ITINERARIO,
               PP.COD_PRODUTO PRODUTO,
               PROD.DSC_PRODUTO DESCRICAO,
               LS.DSC_LINHA_SEPARACAO MAPA,
               PP.DSC_GRADE GRADE,
               SUM(PP.QUANTIDADE) QUANTIDADE,
               F.NOM_FABRICANTE FABRICANTE,
               PDL.NUM_PESO,
               PDL.NUM_LARGURA,
               PDL.NUM_ALTURA,
               PDL.NUM_PROFUNDIDADE,
               PE.DSC_EMBALAGEM DSC_VOLUME,
               PE.IND_PADRAO,
               P.CENTRAL_ENTREGA,
               CASE WHEN CORTE.QTD_CORTE IS NULL THEN 0
                    ELSE CORTE.QTD_CORTE
               END AS QTD_CORTE,
               CASE WHEN LS.COD_LINHA_SEPARACAO = 13 THEN 0
                    WHEN LS.COD_LINHA_SEPARACAO = 15 THEN 0
                    ELSE 1
               END AS SEQ_QUEBRA
          FROM CARGA C
         INNER JOIN PEDIDO P
            ON P.COD_CARGA = C.COD_CARGA
         INNER JOIN ITINERARIO I
            ON P.COD_ITINERARIO = I.COD_ITINERARIO
         INNER JOIN PEDIDO_PRODUTO PP
            ON PP.COD_PEDIDO = P.COD_PEDIDO
         INNER JOIN PRODUTO PROD
            ON PP.COD_PRODUTO = PROD.COD_PRODUTO
           AND PP.DSC_GRADE  = PROD.DSC_GRADE
          LEFT JOIN FABRICANTE F
            ON PROD.COD_FABRICANTE = F.COD_FABRICANTE
          LEFT JOIN LINHA_SEPARACAO LS
            ON PROD.COD_LINHA_SEPARACAO = LS.COD_LINHA_SEPARACAO
         INNER JOIN PRODUTO_EMBALAGEM PE
            ON PE.COD_PRODUTO = PROD.COD_PRODUTO
           AND PE.DSC_GRADE  = PROD.DSC_GRADE
         INNER JOIN PRODUTO_DADO_LOGISTICO PDL
            ON PDL.COD_PRODUTO_EMBALAGEM = PE.COD_PRODUTO_EMBALAGEM
          LEFT JOIN (SELECT COUNT (DISTINCT ES.COD_PRODUTO_EMBALAGEM) AS QTD_CORTE,
                            ES.COD_PRODUTO,
                            ES.DSC_GRADE,
                            ES.COD_PEDIDO
                       FROM ETIQUETA_SEPARACAO ES
                      WHERE ES.COD_STATUS IN (524,525)
                        AND ES.COD_PRODUTO_VOLUME IS NULL
                      GROUP BY ES.COD_PRODUTO, ES.DSC_GRADE, ES.COD_PEDIDO) CORTE
            ON CORTE.COD_PRODUTO = PP.COD_PRODUTO
           AND CORTE.DSC_GRADE = PP.DSC_GRADE
           AND CORTE.COD_PEDIDO = PP.COD_PEDIDO
         GROUP BY
               C.COD_EXPEDICAO,
               C.COD_CARGA,
               C.DSC_PLACA_EXPEDICAO,
               C.COD_CARGA_EXTERNO,
               P.DSC_LINHA_ENTREGA,
               I.DSC_ITINERARIO,
               I.COD_ITINERARIO,
               PP.COD_PRODUTO,
               PP.DSC_GRADE,
               LS.DSC_LINHA_SEPARACAO,
               PROD.DSC_PRODUTO,
               F.NOM_FABRICANTE,
               PDL.NUM_PESO,
               PDL.NUM_LARGURA,
               PDL.NUM_ALTURA,
               PDL.NUM_PROFUNDIDADE,
               PE.DSC_EMBALAGEM,
               PE.IND_PADRAO,
               P.CENTRAL_ENTREGA,
               CORTE.QTD_CORTE,
               LS.COD_LINHA_SEPARACAO
    ) BASE
WHERE BASE.QTD_CORTE < BASE.QUANTIDADE
ORDER BY
      SEQ_QUEBRA,
      MAPA,
      PRODUTO,
      GRADE,
      IND_PADRAO DESC,
      DSC_VOLUME;