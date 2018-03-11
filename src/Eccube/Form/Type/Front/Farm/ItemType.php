<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/13/2018
 * Time: 4:08 PM
 */

namespace Eccube\Form\Type\Front\Farm;


use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ItemType extends AbstractType
{
    /**
     * @var Application
     */
    public $app;

    /**
     * ProductType constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var ArrayCollection $arrCategory array of category
         */
        $arrCategory = $this->app['eccube.repository.category']->getList(null, true);

        $builder
            // 商品規格情報
            ->add('class', 'item_class', array(
                'mapped' => false,
            ))
            // 基本情報
            ->add('name', 'text', array(
                'label' => 'タイトル',
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('product_image', 'file', array(
                'label' => '商品画像',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ))
            ->add('description_detail', 'textarea', array(
                'label' => '商品説明',
            ))
            ->add('Category', 'entity', array(
                'class' => 'Eccube\Entity\Category',
                'property' => 'NameWithLevel',
                'label' => '品目',
//                'multiple' => false,
                'mapped' => false,
                // Choices list (overdrive mapped)
                'choices' => $arrCategory,
            ))

            // 詳細な説明
            ->add('Tag', 'tag', array(
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
            ))
            // タグ
            ->add('tags', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            // 画像
            ->add('images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('add_images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('delete_images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('Status', 'disp', array(
            ))
            ->add('ReceiptableDate', 'entity', array(
                'label' => '発送可能日',
                'class' => 'Eccube\Entity\Master\ReceiptableDate',
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ));

        $builder->addEventListener(FormEvents::POST_SUBMIT, function ($event) {
            $form = $event->getForm();
            $data = $form->getData();
            $class = $data->getProductClasses()[0];

            $interval = \DateInterval::createFromDateString('1 day');
            $dateEnd = clone $class->getProductionEndDate();
            $period = new \DatePeriod($class->getProductionStartDate(), $interval, $dateEnd->modify('+1 day'));
            $arrDateId = array();
            /** @var \DateTime $date */
            foreach ($period as $date) {
                $dateId = $date->format('N');
                $arrDateId[] = $dateId;
            }
            $ReceiptableDates = $form['ReceiptableDate']->getData();
            $arrReceiptableDateId = array();
            foreach ($ReceiptableDates as $receiptableDate) {
                $arrReceiptableDateId[] = $receiptableDate->getId();
            }
            if (count(array_diff($arrReceiptableDateId, $arrDateId)) > 0) {
                $form['ReceiptableDate']->addError(new FormError('発送可能日は生産予定期間内ではありません。'));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'item_edit';
    }
}