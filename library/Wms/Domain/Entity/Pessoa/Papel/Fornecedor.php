<?php
namespace Wms\Domain\Entity\Pessoa\Papel;

use Wms\Domain\Entity\Pessoa;

/**
 * Fornecedor
 *
 * @Table(name="FORNECEDOR")
 * @Entity(repositoryClass="Bisna\Base\Domain\Entity\Repository")
 */
class Fornecedor
{

    /**
     * @var integer $id
     * @Column(name="COD_FORNECEDOR", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="SEQUENCE")
     * @SequenceGenerator(sequenceName="SQ_PESSOA_01", initialValue=1, allocationSize=100)
     */
    protected $id;
    /**
     * @var string $idexterno
     * @Column(name="COD_EXTERNO", type="string", nullable=false)
     */
    protected $idExterno;
    /**
     * @OneToOne(targetEntity="Wms\Domain\Entity\Pessoa\Juridica", cascade={"all"}, orphanRemoval=true)
     * @JoinColumn(name="COD_FORNECEDOR", referencedColumnName="COD_PESSOA")
     */
    protected $pessoa;

    public function getId()
    {
	return $this->id;
    }

    public function getPessoa()
    {
	return $this->pessoa;
    }

    public function setPessoa($pessoa)
    {
	$this->pessoa = $pessoa;
        return $this;
    }

    public function getIdExterno()
    {
	return $this->idExterno;
    }

    public function setIdExterno($idExterno)
    {
	$this->idExterno = $idExterno;
        return $this;
    }

}