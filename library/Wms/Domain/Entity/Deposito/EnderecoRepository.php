<?php

namespace Wms\Domain\Entity\Deposito;

use Doctrine\ORM\EntityRepository,
    Wms\Domain\Entity\Deposito\Endereco as EnderecoEntity,
    Wms\Util\Endereco as EnderecoUtil,
    Core\Util\Converter;

/**
 * Endereco
 *
 */
class EnderecoRepository extends EntityRepository
{

    /**
     * Checa se existem enderecos com os mesmos valores de enderecamento
     * e diferentes caracteristicas
     *
     * @param array $values
     * @return boolean Caso faixa de endereco ok retorna verdadeiro
     */
    public function checarEndereco(array $values)
    {
        extract($values['identificacao']);
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT DISTINCT e.idAreaArmazenagem, e.idCaracteristica, e.idEstruturaArmazenagem, e.idTipoEndereco
            FROM wms:Deposito\Endereco e
            WHERE e.deposito = :idDeposito
                AND e.rua BETWEEN :inicialRua AND :finalRua
                AND e.predio BETWEEN :inicialPredio AND :finalPredio
                AND e.nivel BETWEEN :inicialNivel AND :finalNivel
                AND e.apartamento BETWEEN :inicialApartamento AND :finalApartamento');

        $query->setParameter('idDeposito', $idDeposito)
            ->setParameter('inicialRua', $inicialRua)
            ->setParameter('finalRua', $finalRua)
            ->setParameter('inicialPredio', $inicialPredio)
            ->setParameter('finalPredio', $finalPredio)
            ->setParameter('inicialNivel', $inicialNivel)
            ->setParameter('finalNivel', $finalNivel)
            ->setParameter('inicialApartamento', $inicialApartamento)
            ->setParameter('finalApartamento', $finalApartamento);

        if (!empty($lado)) {
            if ($lado == "P")
                $query->andWhere("MOD(e.predio,2) = 0");
            if ($lado == "I")
                $query->andWhere("MOD(e.predio,2) = 1");
        }

        $enderecos = $query->getArrayResult();

        return (count($enderecos) > 0) ? true : false;
    }

    /**
     *
     * @param EnderecoEntity $enderecoEntity
     * @param array $values
     * @throws \Exception
     */
    public function save(EnderecoEntity $enderecoEntity, array $values)
    {
        extract($values['identificacao']);
        $em = $this->getEntityManager();
        $view = new \Zend_View;

        $paramsUrl = $values['identificacao'];
        $paramsUrl['controller'] = 'endereco';
        $paramsUrl['action'] = 'listar-existentes-ajax';

        $deposito = $em->getReference('wms:Deposito', $idDeposito);
        $caracteristica = $em->getReference('wms:Deposito\Endereco\Caracteristica', $idCaracteristica);
        $estruturaArmazenagem = $em->getReference('wms:Armazenagem\Estrutura\Tipo', $idEstruturaArmazenagem);
        $tipoEndereco = $em->getReference('wms:Deposito\Endereco\Tipo', $idTipoEndereco);
        $areaArmazenagem = $em->getReference('wms:Deposito\AreaArmazenagem', $idAreaArmazenagem);

        //echo $ativo;exit;

        //caso edicao
        if (!empty($id)) {
            $enderecoEntity = $em->getReference('wms:Deposito\Endereco', $id);

            $enderecoEntity->setSituacao($situacao);
            $enderecoEntity->setDeposito($deposito);
            $enderecoEntity->setCaracteristica($caracteristica);
            $enderecoEntity->setEstruturaArmazenagem($estruturaArmazenagem);
            $enderecoEntity->setTipoEndereco($tipoEndereco);
            $enderecoEntity->setStatus($status);
            $enderecoEntity->setAtivo($ativo);
            $enderecoEntity->setAreaArmazenagem($areaArmazenagem);

            $em->persist($enderecoEntity);
        } else {
            //loop de rua
            for ($auxRua = $inicialRua; $auxRua <= $finalRua; $auxRua++) {
                //loop de predio
                for ($auxPredio = $inicialPredio; $auxPredio <= $finalPredio; $auxPredio++) {
                    //loop de nivel
                    for ($auxNivel = $inicialNivel; $auxNivel <= $finalNivel; $auxNivel++) {
                        //loop de apartamento
                        for ($auxApto = $inicialApartamento; $auxApto <= $finalApartamento; $auxApto++) {

                            //checa o cadastro dos lados
                            if (isset($lado) && (($lado == 'I' && !($auxPredio % 2)) || ($lado == 'P' && ($auxPredio % 2))))
                                continue;

                            //procura um endereco existente com as caracteristicas
                            $enderecoEntity = $this->findOneBy(array(
                                'idDeposito' => $idDeposito,
                                'rua' => $auxRua,
                                'predio' => $auxPredio,
                                'nivel' => $auxNivel,
                                'apartamento' => $auxApto,
                            ));

                            //cria um objeto caso n encontre->get
                            if ($enderecoEntity == null)
                                $enderecoEntity = new EnderecoEntity;
                            else {
                                //enderecosExistentes
                                if (!in_array($enderecoEntity->getId(), $enderecosSobrepor))
                                    continue;
                            }

                            $dscEndereco = array(
                                'RUA' => $auxRua,
                                'PREDIO' => $auxPredio,
                                'NIVEL' => $auxNivel,
                                'APTO' => $auxApto);

                            $dscEndereco = EnderecoUtil::formatar($dscEndereco);

                            $enderecoEntity->setRua($auxRua)
                            ->setPredio($auxPredio)
                            ->setNivel($auxNivel)
                            ->setApartamento($auxApto)
                            ->setSituacao($situacao)
                            ->setDeposito($deposito)
                            ->setCaracteristica($caracteristica)
                            ->setEstruturaArmazenagem($estruturaArmazenagem)
                            ->setTipoEndereco($tipoEndereco)
                            ->setStatus($status)
                            ->setAreaArmazenagem($areaArmazenagem)
                            ->setDescricao($dscEndereco)
                            ->setAtivo($ativo);


                            $em->persist($enderecoEntity);
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @param int $id
     */
    public function remove($id)
    {
        $em = $this->getEntityManager();
        $auxPredioroxy = $em->getReference('wms:Deposito\Endereco', $id);
        $em->remove($auxPredioroxy);
    }

    /**
     *
     * @return type
     */
    public function getRuas()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $auxRuauas = $qb->select('DISTINCT e.rua')
            ->from('wms:Deposito\Endereco', 'e')
            ->orderBy('e.rua');

        return $auxRuauas->getQuery()->getResult();
    }

    /**
     * Verifica se existem o endereco informado
     * @param String $endereco
     * @return boolean Caso exista o endereco retorna true
     */
    public function verificarEndereco($endereco)
    {
        $em = $this->getEntityManager();

        $endereco = EnderecoUtil::separar($endereco);

        $dql = $em->createQueryBuilder()
            ->select('e.id')
            ->from('wms:Deposito\Endereco', 'e')
            ->where("e.rua = '" . $endereco['RUA'] . "'")
            ->andWhere("e.predio = '" . $endereco['PREDIO'] . "'")
            ->andWhere("e.nivel = '" . $endereco['NIVEL'] . "'")
            ->andWhere("e.apartamento = '" . $endereco['APTO'] . "'");

        $enderecos = $dql->getQuery()->getResult();

        return (count($enderecos) > 0) ? true : false;
    }

    public function getEnderecoIdByDescricao ($descricao){
        $sql = " SELECT COD_DEPOSITO_ENDERECO, NUM_NIVEL
                 FROM DEPOSITO_ENDERECO
                 WHERE CAST(REPLACE(DSC_DEPOSITO_ENDERECO, '.', '') as INT) = ". $descricao;

        $array = $this->getEntityManager()->getConnection()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        return $array;

    }

    public function getPicking()  {
        $em = $this->getEntityManager();
        $tipoPicking = $this->_em->getRepository('wms:Sistema\Parametro')->findOneBy(array('constante' => 'ID_CARACTERISTICA_PICKING'))->getValor();

        $dql = $em->createQueryBuilder()
            ->select('e.descricao as DESCRICAO, MOD(e.predio,2) as lado')
            ->from('wms:Deposito\Endereco', 'e')
            ->orderBy("e.rua, lado , e.predio, e.apartamento")
            ->where("e.idCaracteristica = '" . $tipoPicking . "'")
            ->groupBy("e.descricao, e.predio");
        $enderecos = $dql->getQuery()->getResult();

        return $enderecos;
    }

    public function getEnderecoByProduto($idProduto, $grade) {
        $em = $this->getEntityManager();
        $produtoEn = $em->getRepository("wms:Produto")->findOneBy(array('id' => $idProduto, 'grade' => $grade));

        if (count($produtoEn->getEmbalagens()) <=0) {
            $dql = $em->createQueryBuilder()
                ->select('e.descricao as DESCRICAO, e.id')
                ->from("wms:Produto\Volume", "pv")
                ->innerJoin("pv.endereco", "e")
                ->innerJoin("pv.produto", "p")
                ->where("p.id = $idProduto")
                ->andWhere("p.grade = '$grade'")
                ->groupBy("e.descricao, e.id");
        } else {
            $dql = $em->createQueryBuilder()
                ->select('e.descricao as DESCRICAO, e.id')
                ->from("wms:Produto\Embalagem", "pe")
                ->innerJoin("pe.endereco", "e")
                ->innerJoin("pe.produto", "p")
                ->where("p.id = $idProduto")
                ->andWhere("p.grade = '$grade'")
                ->groupBy("e.descricao, e.id");
        }

        $enderecos = $dql->getQuery()->getResult();

        return $enderecos;
    }

    public function getEnderecosAlocados() {
        $sql = "
            SELECT DESCRICAO
            FROM (
                        SELECT DISTINCT DSC_DEPOSITO_ENDERECO as DESCRICAO, MOD(DE.NUM_PREDIO,2) as LADO, DE.NUM_RUA, DE.NUM_PREDIO, DE.NUM_APARTAMENTO
                          FROM DEPOSITO_ENDERECO DE
                         WHERE DE.COD_DEPOSITO_ENDERECO IN (SELECT DE2.COD_DEPOSITO_ENDERECO
                                                              FROM PRODUTO_EMBALAGEM PE
                                                        INNER JOIN DEPOSITO_ENDERECO DE2 ON DE2.COD_DEPOSITO_ENDERECO = PE.COD_DEPOSITO_ENDERECO)
                            OR DE.COD_DEPOSITO_ENDERECO IN (SELECT DE3.COD_DEPOSITO_ENDERECO
                                                              FROM PRODUTO_VOLUME PV
                                                        INNER JOIN DEPOSITO_ENDERECO DE3 ON DE3.COD_DEPOSITO_ENDERECO = PV.COD_DEPOSITO_ENDERECO)
            ) a
            ORDER BY NUM_RUA, LADO , NUM_PREDIO, NUM_APARTAMENTO
        ";

        $array = $this->getEntityManager()->getConnection()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        return $array;
    }

    public function getProdutoByEndereco($dscEndereco, $unico = true) {
        $em = $this->getEntityManager();
        $tempEndereco = "a";

        if (strlen($dscEndereco) < 8) {
            $rua = 0;
            $predio = 0;
            $nivel = 0;
            $apartamento = 0;
        } else {
            //var_dump(str_replace('.','',$dscEndereco));exit;
            $dscEndereco = str_replace('.','',$dscEndereco);
            if (strlen($dscEndereco) == 8){
                $tempEndereco = "0" . $dscEndereco;
            } else {
                $tempEndereco = $dscEndereco;
            }
            $rua = intval( substr($tempEndereco,0,2));
            $predio = intval(substr($tempEndereco,2,3));
            $nivel =  intval(substr($tempEndereco,5,2));
            $apartamento = intval(substr($tempEndereco,7,2));
        }

        $dql = $em->createQueryBuilder()
            ->select('p.id as codProduto, p.grade, p.descricao' )
            ->distinct(true)
            ->from("wms:Produto\Volume", "pv")
            ->InnerJoin("pv.endereco", "e")
            ->InnerJoin("pv.produto", "p")
            ->where("e.rua = $rua")
            ->andWhere("e.predio = $predio")
            ->andWhere("e.nivel = $nivel")
            ->andWhere("e.apartamento = $apartamento");
        if ($unico == true) {
            $produto = $dql->getQuery()->setMaxResults(1)->getArrayResult();
        } else {
            $produto = $dql->getQuery()->getArrayResult();
        }

        if (count($produto) <= 0) {
            $dql = $em->createQueryBuilder()
                ->select('p.id as codProduto, p.grade, p.descricao')
                ->distinct(true)
                ->from("wms:Produto\Embalagem", "pe")
                ->leftJoin("pe.endereco", "e")
                ->leftJoin("pe.produto", "p")
                ->where("e.rua = $rua")
                ->andWhere("e.predio = $predio")
                ->andWhere("e.nivel = $nivel")
                ->andWhere("e.apartamento = $apartamento");
            if ($unico == true) {
                $produto = $dql->getQuery()->setMaxResults(1)->getArrayResult();
            } else {
                $produto = $dql->getQuery()->getArrayResult();
            }
        }
        return $produto;

    }

    public function getEnderecoesDisponivesByParam($params) {
        extract($params);
        $query = "
         SELECT DE.COD_DEPOSITO_ENDERECO,
                DE.DSC_DEPOSITO_ENDERECO,
                CA.DSC_CARACTERISTICA_ENDERECO,
                AA.DSC_AREA_ARMAZENAGEM,
                TP.DSC_TIPO_EST_ARMAZ,
                TE.DSC_TIPO_ENDERECO,
                DE.NUM_RUA,
                DE.NUM_PREDIO,
                DE.NUM_NIVEL,
                LONGARINA.TAMANHO_LONGARINA - LONGARINA.OCUPADO as TAMANHO_DISPONIVEL
           FROM DEPOSITO_ENDERECO DE
          INNER JOIN CARACTERISTICA_ENDERECO CA ON DE.COD_CARACTERISTICA_ENDERECO = CA.COD_CARACTERISTICA_ENDERECO
          INNER JOIN AREA_ARMAZENAGEM AA        ON DE.COD_AREA_ARMAZENAGEM = AA.COD_AREA_ARMAZENAGEM
          INNER JOIN TIPO_EST_ARMAZ TP          ON DE.COD_TIPO_EST_ARMAZ = TP.COD_TIPO_EST_ARMAZ
          INNER JOIN TIPO_ENDERECO TE           ON DE.COD_TIPO_ENDERECO = TE.COD_TIPO_ENDERECO
          INNER JOIN V_OCUPACAO_LONGARINA LONGARINA
		          ON LONGARINA.NUM_PREDIO = DE.NUM_PREDIO
                 AND LONGARINA.NUM_NIVEL  = DE.NUM_NIVEL
                 AND LONGARINA.NUM_RUA    = DE.NUM_RUA
          WHERE DE.NUM_NIVEL != 0 AND DE.IND_ATIVO = 'S'
        ";

        if (!empty($unitizador)) {
            $unitizadorEn = $this->getEntityManager()->getRepository("wms:Armazenagem\Unitizador")->find($unitizador);
            $larguraUnitizador = $unitizadorEn->getLargura(false) * 100;
            $query = $query . " AND ((LONGARINA.TAMANHO_LONGARINA - LONGARINA.OCUPADO) >= $larguraUnitizador)";
		}

		if ($ocupado == 'D') {
            $query = $query . " AND DE.IND_DISPONIVEL = 'S'";
		}
		if ($ocupado == 'O') {
            $query = $query . " AND DE.IND_DISPONIVEL = 'N'";
		}

        if (!empty ($inicialRua)) {
            $query = $query . " AND DE.NUM_RUA >= $inicialRua";
        }
        if (!empty ($finalRua)) {
            $query = $query . " AND DE.NUM_RUA <= $finalRua";
        }
        if (!empty ($inicialPredio)) {
            $query = $query . " AND DE.NUM_PREDIO >= $inicialPredio";
        }
        if (!empty ($finalPredio)) {
            $query = $query . " AND DE.NUM_PREDIO <= $finalPredio";
        }
        if (!empty ($inicialNivel)) {
            $query = $query . " AND DE.NUM_NIVEL >= $inicialNivel";
        }
        if (!empty ($finalNivel)) {
            $query = $query . " AND DE.NUM_NIVEL <= $finalNivel";
        }
        if (!empty ($inicialApartamento)) {
            $query = $query . " AND DE.NUM_APARTAMENTO >= $inicialApartamento";
        }
        if (!empty ($finalApartamento)) {
            $query = $query . " AND DE.NUM_APARAMENTO >= $finalApartamento";
        }

        if (!empty($lado)) {
            if ($lado == "P")
                $query = $query . " AND MOD(DE.NUM_PREDIO,2) = 0";
            if ($lado == "I")
                $query = $query . " AND MOD(DE.NUM_PREDIO,2) = 1";
        }

        if (!empty($situacao))
            $query = $query . " AND DE.IND_SITUACAO = $situacao";
        if (!empty($status))
            $query = $query . " AND DE.IND_STATUS = $status";
        if (!empty($idCaracteristica))
            $query = $query . " AND DE.COD_CARACTERISTICA_ENDERECO = $idCaracteristica";
        if (!empty($idEstruturaArmazenagem))
            $query = $query . " AND DE.COD_TIPO_EST_ARMAZ = $idEstruturaArmazenagem";
        if (!empty($idTipoEndereco))
            $query = $query . " AND DE.COD_TIPO_ENDERECO = $idTipoEndereco";
        if (!empty($idAreaArmazenagem))
            $query = $query . " AND DE.COD_AREA_ARMAZENAGEM = $idAreaArmazenagem";

        $query = $query . "  ORDER BY (LONGARINA.TAMANHO_LONGARINA - LONGARINA.OCUPADO), DE.NUM_RUA, DE.NUM_PREDIO, DE.NUM_NIVEL";

        $array = $this->getEntityManager()->getConnection()->query($query)-> fetchAll(\PDO::FETCH_ASSOC);
       return $array;

    }

    public function getEnderecosAdjacentes ($predio, $rua,$nivel, $apartamento, $qtdAdjacentes) {
        $sql = "
            SELECT * FROM (
            SELECT DISTINCT * FROM (
            SELECT DE.COD_DEPOSITO_ENDERECO,
                   CASE WHEN E.COD_DEPOSITO_ENDERECO IS NULL THEN 'S'
                        ELSE 'N'
                   END AS DISPONIVEL,
                   DE.NUM_APARTAMENTO
             FROM DEPOSITO_ENDERECO DE
             LEFT JOIN ESTOQUE E ON E.COD_DEPOSITO_ENDERECO = DE.COD_DEPOSITO_ENDERECO
            WHERE DE.NUM_PREDIO = $predio
              AND DE.NUM_NIVEL = $nivel
              AND DE.NUM_RUA = $rua
              AND DE.NUM_APARTAMENTO >= $apartamento)
              ORDER BY CAST(NUM_APARTAMENTO AS INT) ASC) WHERE ROWNUM <= $qtdAdjacentes
              ";

        $array = $this->getEntityManager()->getConnection()->query($sql)-> fetchAll(\PDO::FETCH_ASSOC);
        return $array;

    }

    public function enderecoOcupado($enderecoId) {
        $estoqueRepo = $this->getEntityManager()->getRepository("wms:Enderecamento\Estoque");
        $estoquesEn = $estoqueRepo->findBy(array('depositoEndereco'=> $enderecoId));

        if (count($estoquesEn) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function ocuparLiberarEnderecosAdjacentes($idEndereco, $qtdAdjacente, $operacao = "OCUPAR") {

        $enderecoEn = $this->findOneBy(array('id'=>$idEndereco));
        $predio = $enderecoEn->getPredio();
        $rua = $enderecoEn->getRua();
        $nivel = $enderecoEn->getNivel();

        //Só continua se não for picking
        if ($enderecoEn->getNivel() == '00') {
            return true;
        }

        $apartamento = $enderecoEn->getApartamento();
        $enderecosAjacentes = $this->getEnderecosAdjacentes($predio, $rua, $nivel, $apartamento, $qtdAdjacente);

        foreach ($enderecosAjacentes as $enderecoAjacente) {
            $enderecoAdjacenteEn = $this->findOneBy(array('id'=>$enderecoAjacente['COD_DEPOSITO_ENDERECO']));
            if ($operacao == "OCUPAR") {
                $enderecoAdjacenteEn->setDisponivel("N");
            } else {
                $enderecoAdjacenteEn->setDisponivel($enderecoAjacente['DISPONIVEL']);
            }
            $this->getEntityManager()->persist($enderecoAdjacenteEn);
        }

    }

    public function getTamanhoDisponivelByPredio ( $rua ,$predio, $nivel) {
        $tamanhoLongarinaRepo = $this->getEntityManager()->getRepository("wms:Deposito\Endereco\TamanhoLongarina");
        $longarinaEn = $tamanhoLongarinaRepo->findOneBy(array('predio'=> $predio, 'rua' =>$rua));

        if ($longarinaEn != NULL) {
            $tamanhoLongarina = $longarinaEn->getTamanho();
        } else {
            $parametro = $this->_em->getRepository('wms:Sistema\Parametro')->findOneBy(array('constante' => 'TAMANHO_LONGARINA_PADRAO'));
            $tamanhoLongarina = $parametro->getValor();
        }

        $sql = "SELECT SUM(U.NUM_LARGURA_UNITIZADOR) as OCUPADO
                  FROM DEPOSITO_ENDERECO DE
                  LEFT JOIN ESTOQUE E ON E.COD_DEPOSITO_ENDERECO = DE.COD_DEPOSITO_ENDERECO
                  LEFT JOIN UNITIZADOR U ON E.COD_UNITIZADOR = U.COD_UNITIZADOR
                 WHERE DE.NUM_PREDIO = $predio
                   AND DE.NUM_NIVEL = $nivel
                   AND DE.NUM_RUA = $rua";
        $result = $this->getEntityManager()->getConnection()->query($sql)-> fetchAll(\PDO::FETCH_ASSOC);

        if ($result[0]['OCUPADO'] == NULL) {
            $ocupado = 0;
        } else {
            $ocupado = $result[0]['OCUPADO'] * 100;
        }

        $disponivel = $tamanhoLongarina - $ocupado;

        return $disponivel;
    }

    public function getEndereco($rua, $predio, $nivel, $apto)
    {
        $source = $this->_em->createQueryBuilder()
            ->select("e.id, e.descricao")
            ->from('wms:Deposito\Endereco', 'e')
            ->andWhere("e.rua = $rua")
            ->andWhere("e.predio = $predio")
            ->andWhere("e.nivel = $nivel")
            ->andWhere("e.apartamento = $apto");

        return $source->getQuery()->getResult();

    }

    public function getOcupacaoRuaReport($params)
    {
        $ruaInicial = $params['ruaInicial'];
        $ruaFinal   = $params['ruaFinal'];

        $sqlWhere = "";
        if ($ruaFinal != "") {
            if ($sqlWhere != "") {$sqlWhere = $sqlWhere . " AND ";}
            $sqlWhere = $sqlWhere . " DE.NUM_RUA <= " . $ruaFinal;
        }
        if ($ruaInicial != "") {
            if ($sqlWhere != "") {$sqlWhere = $sqlWhere . " AND ";}
            $sqlWhere = $sqlWhere . " DE.NUM_RUA >= " . $ruaInicial;
        }
        if ($sqlWhere != "") {
            $sqlWhere = " WHERE " . $sqlWhere . " and DE.IND_ATIVO='S' ";
        } else {
            $sqlWhere = " WHERE DE.IND_ATIVO='S' ";
        }

        $sql= "SELECT OC.NUM_RUA as RUA,
                      DE.QTD_POSICOES as PALETES_EXISTENTES,
                      OC.QTD_OCUPADO as PALETES_OCUPADOS,
                      ROUND(((OC.QTD_OCUPADO * 100) / DE.QTD_POSICOES), 2) PERCENTUAL_OCUPADOS
                 FROM (SELECT COUNT(DISTINCT DE.COD_DEPOSITO_ENDERECO) as QTD_POSICOES, NUM_RUA, DE.IND_ATIVO
                         FROM V_SALDO_ESTOQUE_COMPLETO P LEFT JOIN DEPOSITO_ENDERECO DE ON DE.COD_DEPOSITO_ENDERECO = P.COD_DEPOSITO_ENDERECO
                          LEFT JOIN UNITIZADOR u ON u.COD_UNITIZADOR=P.COD_UNITIZADOR
                          WHERE DE.COD_CARACTERISTICA_ENDERECO <>37
                          AND ( ( u.COD_UNITIZADOR=23 and DE.NUM_APARTAMENTO=3 and DE.IND_DISPONIVEL = 'N' )  or DE.NUM_APARTAMENTO<>3 or ( DE.NUM_APARTAMENTO=3 and u.COD_UNITIZADOR<>23 ) )
                        GROUP BY DE.NUM_RUA,DE.IND_ATIVO) DE
                LEFT JOIN (SELECT COUNT(DISTINCT DE.COD_DEPOSITO_ENDERECO) as QTD_OCUPADO,
                                                  DE.NUM_RUA
                                             FROM V_SALDO_ESTOQUE_COMPLETO P
                                             LEFT JOIN DEPOSITO_ENDERECO DE ON DE.COD_DEPOSITO_ENDERECO = P.COD_DEPOSITO_ENDERECO
                                             LEFT JOIN UNITIZADOR u ON u.COD_UNITIZADOR=P.COD_UNITIZADOR
                                            WHERE P.COD_DEPOSITO_ENDERECO IS NOT NULL AND P.QTDE>0 AND DE.COD_CARACTERISTICA_ENDERECO <>37
                                             AND ( ( u.COD_UNITIZADOR=23 and DE.NUM_APARTAMENTO=3 and DE.IND_DISPONIVEL = 'N' )  or DE.NUM_APARTAMENTO<>3 or ( DE.NUM_APARTAMENTO=3 and u.COD_UNITIZADOR<>23 ) )
                                            GROUP BY DE.NUM_RUA) OC
                   ON OC.NUM_RUA = DE.NUM_RUA
              ".$sqlWhere."
               GROUP BY
                OC.NUM_RUA,
                      DE.QTD_POSICOES ,
                      OC.QTD_OCUPADO
               ORDER BY OC.NUM_RUA";

        $result = $this->getEntityManager()->getConnection()->query($sql)-> fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getValidaTamanhoEndereco($idEndereco, $larguraPalete) {
        $longarinaRepo   = $this->getEntityManager()->getRepository("wms:Armazenagem\VOcupacaoLongarina");
        $estoqueRepo     = $this->getEntityManager()->getRepository("wms:Enderecamento\Estoque");

        $tamanhoUnitizadorAlocado = 0;
        $estoquesEn = $estoqueRepo->findBy(array('depositoEndereco'=>$idEndereco));
        foreach($estoquesEn as $estoqueEn){
            $unitizadorEn = $estoqueEn->getUnitizador();
            if ($unitizadorEn != NULL) {
                $tamanhoUnitizador = $unitizadorEn->getLargura(false) * 100;
                if ($tamanhoUnitizador > $tamanhoUnitizadorAlocado) {
                    $tamanhoUnitizadorAlocado = $tamanhoUnitizador;
                }
            }
        }

        /** @var \Wms\Domain\Entity\Deposito\Endereco $enderecoEn */
        $enderecoEn = $this->findOneBy(array('id'=>$idEndereco));
        $rua = $enderecoEn->getRua();
        $predio = $enderecoEn->getPredio();
        $nivel = $enderecoEn->getNivel();

        /** @var \Wms\Domain\Entity\Armazenagem\VOcupacaoLongarina $longarinaEn */
        $longarinaEn      = $longarinaRepo->findOneBy(array('rua'=>$rua,'predio'=>$predio,'nivel'=>$nivel));
        $larguraLongarina = $longarinaEn->getTamanho();
        $tamanhoOcupado   = $longarinaEn->getQtdOcupada() - $tamanhoUnitizadorAlocado;

        if ($tamanhoUnitizadorAlocado > $larguraPalete) {
            $larguraPalete = $tamanhoUnitizadorAlocado;
        }

        if (($tamanhoOcupado + ($larguraPalete)) > $larguraLongarina) {
            return false;
        }

        return true;
    }

    public function getOcupacaoPeriodoResumidoReport ($params) {
        $dataInicial = $params['dataInicial1'];
        $dataFinal = $params['dataInicial2'];
        $ruaInicial = $params['ruaInicial'];
        $ruaFinal   = $params['ruaFinal'];

        $sqlWhere = "";
        if ($ruaFinal != "") {
            $sqlWhere = $sqlWhere . " AND P.NUM_RUA <= " . $ruaFinal." ";
        }
        if ($ruaInicial != "") {
            $sqlWhere = $sqlWhere . " AND P.NUM_RUA >= " . $ruaInicial." ";
        }

        $sql  = "SELECT NUM_RUA,
                        QTD_EXISTENTES,
                        QTD_OCUPADOS,
                        QTD_VAZIOS,
                        OCUPACAO,
                        TO_CHAR(DTH_ESTOQUE,'DD/MM/YYYY') as DTH_ESTOQUE
                   FROM POSICAO_ESTOQUE_RESUMIDO P
                  WHERE (P.DTH_ESTOQUE BETWEEN TO_DATE('$dataInicial 00:00', 'DD-MM-YYYY HH24:MI')
                    AND TO_DATE('$dataFinal 23:59', 'DD-MM-YYYY HH24:MI'))
                        $sqlWhere
                  ORDER BY DTH_ESTOQUE, TO_NUMBER(P.NUM_RUA)";

        $result = $this->getEntityManager()->getConnection()->query($sql)-> fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function getOcupacaoPeriodoReport($params)
    {
        extract($params);

        $dataInicial = $params['dataInicial1'];
        $dataFinal = $params['dataInicial2'];
        $ruaInicial = $params['ruaInicial'];
        $ruaFinal   = $params['ruaFinal'];
        $tipoPicking = $this->getSystemParameterValue('ID_CARACTERISTICA_PICKING');

        $sqlWhere = "";
        if ($ruaFinal != "") {
            $sqlWhere = $sqlWhere . " AND HIST.NUM_RUA <= " . $ruaFinal." ";
        }
        if ($ruaInicial != "") {
            $sqlWhere = $sqlWhere . " AND HIST.NUM_RUA >= " . $ruaInicial." ";
        }

        $sql= " SELECT TO_CHAR(HIST.DTH_ESTOQUE,'DD/MM/YYYY') as DATA_ESTOQUE,
                       DE.NUM_RUA as RUA,
                       HIST.OCUPADO as PALETES_OCUPADOS,
                       DE.QTD_EXISTENTES as PALETES_EXISTENTES,
                       ROUND((( HIST.OCUPADO/ DE.QTD_EXISTENTES) * 100),2) AS PERCENTUAL_OCUPADOS
                  FROM (
                     SELECT COUNT(DISTINCT DE.COD_DEPOSITO_ENDERECO) as QTD_EXISTENTES, DE.NUM_RUA
                       FROM DEPOSITO_ENDERECO DE WHERE DE.COD_CARACTERISTICA_ENDERECO <> $tipoPicking
                      GROUP BY DE.NUM_RUA) DE
                RIGHT JOIN (
                     SELECT COUNT(DISTINCT PE.COD_DEPOSITO_ENDERECO) as OCUPADO, PE.DTH_ESTOQUE, DE.NUM_RUA
                       FROM POSICAO_ESTOQUE PE
                  LEFT JOIN DEPOSITO_ENDERECO DE ON DE.COD_DEPOSITO_ENDERECO = PE.COD_DEPOSITO_ENDERECO
                      WHERE PE.COD_DEPOSITO_ENDERECO IS NOT NULL AND DE.COD_CARACTERISTICA_ENDERECO <> $tipoPicking
                       AND (PE.DTH_ESTOQUE BETWEEN TO_DATE('$dataInicial 00:00', 'DD-MM-YYYY HH24:MI') AND TO_DATE('$dataFinal 23:59', 'DD-MM-YYYY HH24:MI'))
                   GROUP BY DE.NUM_RUA, PE.DTH_ESTOQUE) HIST
                   ON HIST.NUM_RUA = DE.NUM_RUA
                   $sqlWhere
                   ORDER BY HIST.DTH_ESTOQUE, DE.NUM_RUA";

        $result = $this->getEntityManager()->getConnection()->query($sql)-> fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPickingSemProdutos($params){
        $tipoPicking = $this->getSystemParameterValue('ID_CARACTERISTICA_PICKING');

        $SQLWhere = "";
        if ($params['ruaInicial'] != "") {
            $SQLWhere = $SQLWhere . " AND DE.NUM_RUA >= ". $params['ruaInicial'];
        }
        if ($params['ruaFinal'] != "") {
            $SQLWhere = $SQLWhere . " AND DE.NUM_RUA <= ". $params['ruaFinal'];
        }

        $SQL = " SELECT DSC_DEPOSITO_ENDERECO
                   FROM DEPOSITO_ENDERECO DE
                  WHERE DE.COD_CARACTERISTICA_ENDERECO = $tipoPicking $SQLWhere
                    AND DE.COD_DEPOSITO_ENDERECO NOT IN (SELECT DISTINCT COD_DEPOSITO_ENDERECO
                                                           FROM PRODUTO_EMBALAGEM
                                                          WHERE COD_DEPOSITO_ENDERECO IS NOT NULL)
                    AND DE.COD_DEPOSITO_ENDERECO NOT IN (SELECT DISTINCT COD_DEPOSITO_ENDERECO
                                                           FROM PRODUTO_VOLUME
                                                          WHERE COD_DEPOSITO_ENDERECO IS NOT NULL)
                    AND DE.IND_ATIVO = 'S'
                  ORDER BY DSC_DEPOSITO_ENDERECO
        ";

        $result = $this->getEntityManager()->getConnection()->query($SQL)-> fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }
    public function getPickingMultiplosProdutos($params){

        $SQLWhere = "";
        if ($params['ruaInicial'] != "") {
            $SQLWhere = $SQLWhere . " AND DE.NUM_RUA >= ". $params['ruaInicial'];
        }
        if ($params['ruaFinal'] != "") {
            $SQLWhere = $SQLWhere . " AND DE.NUM_RUA <= ". $params['ruaFinal'];
        }
        $SQL = "
                SELECT DISTINCT P.COD_PRODUTO COD_PRODUTO,
                                P.DSC_PRODUTO PRODUTO,
                                P.DSC_GRADE GRADE,
                                TDE.DESCRICAO,
                                TDE.QTD QTD
                FROM PRODUTO P
                LEFT JOIN PRODUTO_EMBALAGEM PE ON P.COD_PRODUTO = PE.COD_PRODUTO AND P.DSC_GRADE = PE.DSC_GRADE
                LEFT JOIN PRODUTO_VOLUME PV ON P.COD_PRODUTO = PV.COD_PRODUTO AND P.DSC_GRADE = PV.DSC_GRADE
                INNER JOIN (SELECT COUNT(*) AS QTD,
                                   DE.DSC_DEPOSITO_ENDERECO as DESCRICAO,
                                   DE.COD_DEPOSITO_ENDERECO
                            FROM (SELECT DISTINCT P.COD_PRODUTO,
                                                  P.DSC_GRADE,
                                                  NVL(PE.COD_DEPOSITO_ENDERECO, PV.COD_DEPOSITO_ENDERECO) AS COD_DEPOSITO_ENDERECO
                                  FROM PRODUTO P
                                  LEFT JOIN PRODUTO_EMBALAGEM PE ON PE.COD_PRODUTO = P.COD_PRODUTO AND PE.DSC_GRADE = P.DSC_GRADE
                                  LEFT JOIN PRODUTO_VOLUME    PV ON PV.COD_PRODUTO = P.COD_PRODUTO AND PV.DSC_GRADE = P.DSC_GRADE
                                  WHERE PE.COD_DEPOSITO_ENDERECO IS NOT NULL OR PV.COD_DEPOSITO_ENDERECO IS NOT NULL) E
                            LEFT JOIN DEPOSITO_ENDERECO DE ON DE.COD_DEPOSITO_ENDERECO = E.COD_DEPOSITO_ENDERECO
                            GROUP BY DE.DSC_DEPOSITO_ENDERECO, DE.COD_DEPOSITO_ENDERECO
                            HAVING COUNT (*) > 1
                            ORDER BY DSC_DEPOSITO_ENDERECO) TDE
                      ON TDE.COD_DEPOSITO_ENDERECO = PE.COD_DEPOSITO_ENDERECO OR TDE.COD_DEPOSITO_ENDERECO = PV.COD_DEPOSITO_ENDERECO
                LEFT JOIN DEPOSITO_ENDERECO DE ON DE.COD_DEPOSITO_ENDERECO = TDE.COD_DEPOSITO_ENDERECO
                WHERE (PE.COD_DEPOSITO_ENDERECO IS NOT NULL OR PV.COD_DEPOSITO_ENDERECO IS NOT NULL) $SQLWhere
                ORDER BY TDE.DESCRICAO, P.DSC_PRODUTO";

        $result = $this->getEntityManager()->getConnection()->query($SQL)-> fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }
    public function getEnderecoPicking($params)
    {
        extract($params);

        $query = "
           SELECT DISTINCT(DEP.COD_DEPOSITO_ENDERECO) CODIGO, DEP.NUM_RUA RUA, DEP.NUM_PREDIO PREDIO, DEP.NUM_APARTAMENTO APARTAMENTO,
                  DEP.NUM_NIVEL NIVEL, DEP.DSC_DEPOSITO_ENDERECO ENDERECO, PE.COD_PRODUTO EMBALAGEM, PV.COD_PRODUTO VOLUME,
                  P.DSC_PRODUTO PRODUTO
           FROM DEPOSITO_ENDERECO DEP
           LEFT JOIN PRODUTO_EMBALAGEM PE ON DEP.COD_DEPOSITO_ENDERECO =  PE.COD_DEPOSITO_ENDERECO
           LEFT JOIN PRODUTO_VOLUME PV ON DEP.COD_DEPOSITO_ENDERECO =  PV.COD_DEPOSITO_ENDERECO
           LEFT JOIN PRODUTO P ON PE.COD_PRODUTO = P.COD_PRODUTO OR PV.COD_PRODUTO = P.COD_PRODUTO
           WHERE DEP.NUM_NIVEL = 0
        ";

        if (!empty ($params['rua'])) {
            $query = $query . " AND DEP.NUM_RUA >= " . $params['rua'];
        }
        if (!empty ($params['predio'])) {
            $query = $query . " AND DEP.NUM_PREDIO >= " . $params['predio'];
        }
        if (!empty ($params['apartamento'])) {
            $query = $query . " AND DEP.NUM_APARTAMENTO >= " . $params['apartamento'];
        }

        if (!empty ($params['ruafinal'])) {
            $query = $query . " AND DEP.NUM_RUA <= " . $params['ruafinal'];
        }
        if (!empty ($params['prediofinal'])) {
            $query = $query . " AND DEP.NUM_PREDIO <= " . $params['prediofinal'];
        }
        if (!empty ($params['apartamentofinal'])) {
            $query = $query . " AND DEP.NUM_APARTAMENTO <= " . $params['apartamentofinal'];
        }

        if (!empty ($params['lado'])) {
            if ($params['lado'] == "P")
                $query = $query . " AND MOD(NUM_PREDIO,2) = 0";
            if ($params['lado'] == "I")
                $query = $query . " AND MOD(NUM_PREDIO,2) = 1";
        }

        $query = $query . " ORDER BY RUA, PREDIO, NIVEL, APARTAMENTO";

        $result = $this->getEntityManager()->getConnection()->query($query)-> fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function getImprimirEndereco($enderecos)
    {
       $query = "
           SELECT DISTINCT DEP.DSC_DEPOSITO_ENDERECO DESCRICAO
           FROM DEPOSITO_ENDERECO DEP
           LEFT JOIN PRODUTO_EMBALAGEM PE ON DEP.COD_DEPOSITO_ENDERECO =  PE.COD_DEPOSITO_ENDERECO
           LEFT JOIN PRODUTO_VOLUME PV ON DEP.COD_DEPOSITO_ENDERECO =  PV.COD_DEPOSITO_ENDERECO
           LEFT JOIN PRODUTO P ON PE.COD_PRODUTO = P.COD_PRODUTO OR PV.COD_PRODUTO = P.COD_PRODUTO
           WHERE DEP.COD_DEPOSITO_ENDERECO in ($enderecos) ORDER BY DEP.DSC_DEPOSITO_ENDERECO";

        $result = $this->getEntityManager()->getConnection()->query($query)-> fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

}