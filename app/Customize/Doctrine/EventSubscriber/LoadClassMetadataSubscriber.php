<?php
/**
 * @author Dung Le (Le Quoc Dung)
 * @email lqdung1992@gmail.com
 * @github https://github.com/lqdung1992
 * @date 12/16/2019
 */
namespace Customize\Doctrine\EventSubscriber;

use Customize\Service\ClassMetadataService;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Eccube\Common\EccubeConfig;

/**
 * Class LoadClassMetadataSubscriber
 * @package Customize\Doctrine\EventSubscriber
 */
class LoadClassMetadataSubscriber implements EventSubscriber
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var ClassMetadataService
     */
    private $classMetadataService;

    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * DiscriminatorListener constructor.
     * @param AnnotationReader $annotationReader
     * @param ClassMetadataService $classMetadataService
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        AnnotationReader $annotationReader,
        ClassMetadataService $classMetadataService,
        EccubeConfig $eccubeConfig
    ) {
        $this->annotationReader = $annotationReader;
        $this->classMetadataService = $classMetadataService;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @return array|string[]
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    /**
     * Events::loadClassMetadata
     * position: after load class metadata, before final validate
     *
     * @param LoadClassMetadataEventArgs $event
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \ReflectionException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $event->getClassMetadata();
        $className = $classMetadata->name;

        // define a variable for controls
//        if ($this->eccubeConfig->get('enable_override_trait')) {
        // entity traits override
        $reflectionClass = new \ReflectionClass($className);
        // many traits
        foreach ($reflectionClass->getTraits() as $trait) {
            // many attributes need override
            foreach ($trait->getProperties() as $property) {
                // same property name
                if ($reflectionClass->hasProperty($property->getName())) {
                    $this->classMetadataService->buildAnnotation($property, $classMetadata, $className);
                }
            }
        }
//        }

        return;
    }
}
