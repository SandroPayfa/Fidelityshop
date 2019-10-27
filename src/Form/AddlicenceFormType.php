<?php

namespace App\Form;


use App\Entity\Shop;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormBuilderInterface;


class AddlicenceFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $shops)
	{

		$builder
			//->add('pin', TextType::class, [])
			//->add('magasin', TextType::class, [])
//			->add('terminal_id', TextType::class, [])
            ->add('periode',  ChoiceType::class, [
                'choices'  => [
                    '3 Mois' => '3',
                    '6 Mois' => '6',
                    '12 Mois' => '12',
                ],
            ])
            ->add('type',  ChoiceType::class, [
                'choices'  => [
                    'advensys' => 'advensys',
                    'fidelityshop' => 'fidelityshop'
                ],
            ])
            ->add('magasin',  EntityType::class, [
                'class' => Shop::class,
                // this method must return an array of User entities
                'choice_label' => 'name',
            ]);
	}

//	public function configureOptions(OptionsResolver $resolver)
//	{
//		$resolver->setDefaults([
//			'data_class' => Licences::class,
//		]);
//	}
}
