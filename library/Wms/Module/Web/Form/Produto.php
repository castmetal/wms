<?php

namespace Wms\Module\Web\Form;

use Wms\Module\Web\Form,
    Wms\Domain\Entity\Produto as ProdutoEntity,
    Core\Form\SubForm,
    Wms\Module\Web\Form\Subform\Produto\Identificacao as IdentificacaoForm,
    Wms\Module\Web\Form\Subform\Produto\Embalagem as EmbalagemForm,
    Wms\Module\Web\Form\Subform\Produto\Volume as VolumeForm,
    Wms\Module\Web\Form\Subform\Produto\DadosLogisticos as DadosLogisticosForm;

/**
 * Description of Produto
 *
 * @author Renato Medina <medinadato@gmail.com>
 */
class Produto extends Form
{

    public function init()
    {
        //form's attr
        $this->setAttribs(array('id' => 'produto-form', 'class' => 'saveForm'));

        $formIdentificacao = new IdentificacaoForm;
        $formIdentificacao->removeDisplayGroup('identificacao');

        $this->addSubFormTab('Produto', $formIdentificacao, 'produto');
        $this->addSubFormTab('Embalagens', new EmbalagemForm, 'embalagem', 'produto/embalagem-form.phtml');
        $this->addSubFormTab('Volumes', new VolumeForm, 'volume', 'produto/volume-form.phtml');
        $this->addSubFormTab('Dados Logísticos', new DadosLogisticosForm, 'dadoLogistico', 'produto/dado-logistico-form.phtml');
    }

    /**
     *
     * @param ProdutoEntity $produto 
     */
    public function setDefaultsFromEntity(ProdutoEntity $produto)
    {
        $this->getSubForm('produto')->setDefaultsFromEntity($produto);
        $this->getSubForm('embalagem')->setDefaultsFromEntity($produto);
        $this->getSubForm('volume')->setDefaultsFromEntity($produto);
    }

}
