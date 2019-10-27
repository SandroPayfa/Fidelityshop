<?php

namespace App\Form;

use App\Entity\Shop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EditShopFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        foreach ($options as $option)
//        $arr=json_decode($option->getHoraires());
//        $r=$arr[0];

        for($i=1;$i<=7;$i++)
        $builder->add('o_j_'.$i)
            ->add('f_j_'.$i);
        $builder
            ->add('name')
            ->add('image',FileType::class,array('data_class'=> null, 'label' => 'Image','required' => false))
            ->add('address')
            ->add('city')
            ->add('zip')
            ->add('pays')
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shop::class,
        ]);
    }
}