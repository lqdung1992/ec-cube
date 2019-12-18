<?php
/**
 * @author Dung Le (Le Quoc Dung)
 * @email lqdung1992@gmail.com
 * @github https://github.com/lqdung1992
 * @date 12/16/2019
 */
namespace Customize\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use ReflectionProperty;
use Doctrine\ORM\Mapping;

/**
 * Class ClassMetadataService
 * @package Customize\Service
 */
class ClassMetadataService
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * ClassMetadataService constructor.
     * @param AnnotationReader $annotationReader
     */
    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Overdrive all attributes
     * 1. override column field property
     * 2. override association property
     *
     * @override apart of \Doctrine\ORM\Mapping\Driver\AnnotationDriver::loadMetadataForClass
     *
     * @param ReflectionProperty $property
     * @param ClassMetadata|ClassMetadataInfo $classMetadata
     * @param string $className
     * @throws MappingException
     */
    public function buildAnnotation(ReflectionProperty $property, ClassMetadata $classMetadata, string $className)
    {
        // does not have any $property to override
        if (!$classMetadata->hasField($property->getName()) && !$classMetadata->hasAssociation($property->getName())) {
            return;
        }
        $oldAssociationMappings = $classMetadata->getAssociationMappings();
        $oldMappings = $classMetadata->fieldMappings;
        $mapping = [];
        $mapping['fieldName'] = $property->getName();

        // Evaluate @Cache annotation
        if (($cacheAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\Cache::class)) !== null) {
            $mapping['cache'] = $classMetadata->getAssociationCacheDefaults(
                $mapping['fieldName'],
                [
                    'usage' => constant('Doctrine\ORM\Mapping\ClassMetadata::CACHE_USAGE_' . $cacheAnnot->usage),
                    'region' => $cacheAnnot->region,
                ]
            );
        }
        // Check for JoinColumn/JoinColumns annotations
        $joinColumns = [];

        if ($joinColumnAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\JoinColumn::class)) {
            $joinColumns[] = $this->joinColumnToArray($joinColumnAnnot);
        } else if ($joinColumnsAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\JoinColumns::class)) {
            foreach ($joinColumnsAnnot->value as $joinColumn) {
                $joinColumns[] = $this->joinColumnToArray($joinColumn);
            }
        }

        // Field can only be annotated with one of:
        // @Column, @OneToOne, @OneToMany, @ManyToOne, @ManyToMany
        if ($columnAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\Column::class)) {
            if ($columnAnnot->type == null) {
                throw MappingException::propertyTypeIsRequired($className, $property->getName());
            }

            $mapping = $this->columnToArray($property->getName(), $columnAnnot);

            if ($idAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\Id::class)) {
                $mapping['id'] = true;
            }

            if ($generatedValueAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\GeneratedValue::class)) {
                $classMetadata->setIdGeneratorType(constant('Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_' . $generatedValueAnnot->strategy));
            }

            if ($this->annotationReader->getPropertyAnnotation($property, Mapping\Version::class)) {
                $classMetadata->setVersionMapping($mapping);
            }

//            $classMetadata->mapField($mapping);

            // Check for SequenceGenerator/TableGenerator definition
            if ($seqGeneratorAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\SequenceGenerator::class)) {
                $classMetadata->setSequenceGeneratorDefinition(
                    [
                        'sequenceName' => $seqGeneratorAnnot->sequenceName,
                        'allocationSize' => $seqGeneratorAnnot->allocationSize,
                        'initialValue' => $seqGeneratorAnnot->initialValue
                    ]
                );
            } else if ($this->annotationReader->getPropertyAnnotation($property, 'Doctrine\ORM\Mapping\TableGenerator')) {
                throw MappingException::tableIdGeneratorNotImplemented($className);
            } else if ($customGeneratorAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\CustomIdGenerator::class)) {
                $classMetadata->setCustomGeneratorDefinition(
                    [
                        'class' => $customGeneratorAnnot->class
                    ]
                );
            }
        } else if ($oneToOneAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\OneToOne::class)) {
            if ($idAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\Id::class)) {
                $mapping['id'] = true;
            }

            $mapping['targetEntity'] = $oneToOneAnnot->targetEntity;
            $mapping['joinColumns'] = $joinColumns;
            $mapping['mappedBy'] = $oneToOneAnnot->mappedBy;
            $mapping['inversedBy'] = $oneToOneAnnot->inversedBy;
            $mapping['cascade'] = $oneToOneAnnot->cascade;
            $mapping['orphanRemoval'] = $oneToOneAnnot->orphanRemoval;
            $mapping['fetch'] = $this->getFetchMode($className, $oneToOneAnnot->fetch);
//            $classMetadata->mapOneToOne($mapping);
        } else if ($oneToManyAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\OneToMany::class)) {
            $mapping['mappedBy'] = $oneToManyAnnot->mappedBy;
            $mapping['targetEntity'] = $oneToManyAnnot->targetEntity;
            $mapping['cascade'] = $oneToManyAnnot->cascade;
            $mapping['indexBy'] = $oneToManyAnnot->indexBy;
            $mapping['orphanRemoval'] = $oneToManyAnnot->orphanRemoval;
            $mapping['fetch'] = $this->getFetchMode($className, $oneToManyAnnot->fetch);

            if ($orderByAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\OrderBy::class)) {
                $mapping['orderBy'] = $orderByAnnot->value;
            }

//            $classMetadata->mapOneToMany($mapping);
        } else if ($manyToOneAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\ManyToOne::class)) {
            if ($idAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\Id::class)) {
                $mapping['id'] = true;
            }

            $mapping['joinColumns'] = $joinColumns;
            $mapping['cascade'] = $manyToOneAnnot->cascade;
            $mapping['inversedBy'] = $manyToOneAnnot->inversedBy;
            $mapping['targetEntity'] = $manyToOneAnnot->targetEntity;
            $mapping['fetch'] = $this->getFetchMode($className, $manyToOneAnnot->fetch);
//            $classMetadata->mapManyToOne($mapping);
        } else if ($manyToManyAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\ManyToMany::class)) {
            $joinTable = [];

            if ($joinTableAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\JoinTable::class)) {
                $joinTable = [
                    'name' => $joinTableAnnot->name,
                    'schema' => $joinTableAnnot->schema
                ];

                foreach ($joinTableAnnot->joinColumns as $joinColumn) {
                    $joinTable['joinColumns'][] = $this->joinColumnToArray($joinColumn);
                }

                foreach ($joinTableAnnot->inverseJoinColumns as $joinColumn) {
                    $joinTable['inverseJoinColumns'][] = $this->joinColumnToArray($joinColumn);
                }
            }

            $mapping['joinTable'] = $joinTable;
            $mapping['targetEntity'] = $manyToManyAnnot->targetEntity;
            $mapping['mappedBy'] = $manyToManyAnnot->mappedBy;
            $mapping['inversedBy'] = $manyToManyAnnot->inversedBy;
            $mapping['cascade'] = $manyToManyAnnot->cascade;
            $mapping['indexBy'] = $manyToManyAnnot->indexBy;
            $mapping['orphanRemoval'] = $manyToManyAnnot->orphanRemoval;
            $mapping['fetch'] = $this->getFetchMode($className, $manyToManyAnnot->fetch);

            if ($orderByAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\OrderBy::class)) {
                $mapping['orderBy'] = $orderByAnnot->value;
            }

//            $classMetadata->mapManyToMany($mapping);
        } else if ($embeddedAnnot = $this->annotationReader->getPropertyAnnotation($property, Mapping\Embedded::class)) {
            $mapping['class'] = $embeddedAnnot->class;
            $mapping['columnPrefix'] = $embeddedAnnot->columnPrefix;

//            $classMetadata->mapEmbedded($mapping);
        }

        if (isset($oldMappings[$mapping['fieldName']])) {
            $newMapping = $oldMappings;
            $newMapping[$mapping['fieldName']] = array_merge($oldMappings[$mapping['fieldName']], $mapping);
            $classMetadata->fieldMappings = $newMapping;
        } elseif (isset($oldAssociationMappings[$mapping['fieldName']])) {
            $classMetadata->setAssociationOverride($mapping['fieldName'], $mapping);
        }
    }

    /**
     * Attempts to resolve the fetch mode.
     *
     * @param string $className The class name.
     * @param string $fetchMode The fetch mode.
     *
     * @return integer The fetch mode as defined in ClassMetadata.
     *
     * @throws MappingException If the fetch mode is not valid.
     */
    private function getFetchMode($className, $fetchMode)
    {
        if ( ! defined('Doctrine\ORM\Mapping\ClassMetadata::FETCH_' . $fetchMode)) {
            throw MappingException::invalidFetchMode($className, $fetchMode);
        }

        return constant('Doctrine\ORM\Mapping\ClassMetadata::FETCH_' . $fetchMode);
    }

    /**
     * Parse the given JoinColumn as array
     *
     * @param Mapping\JoinColumn $joinColumn
     * @return array
     */
    private function joinColumnToArray(Mapping\JoinColumn $joinColumn)
    {
        return [
            'name' => $joinColumn->name,
            'unique' => $joinColumn->unique,
            'nullable' => $joinColumn->nullable,
            'onDelete' => $joinColumn->onDelete,
            'columnDefinition' => $joinColumn->columnDefinition,
            'referencedColumnName' => $joinColumn->referencedColumnName,
        ];
    }

    /**
     * Parse the given Column as array
     *
     * @param string $fieldName
     * @param Mapping\Column $column
     *
     * @return array
     */
    private function columnToArray($fieldName, Mapping\Column $column)
    {
        $mapping = [
            'fieldName' => $fieldName,
            'type'      => $column->type,
            'scale'     => $column->scale,
            'length'    => $column->length,
            'unique'    => $column->unique,
            'nullable'  => $column->nullable,
            'precision' => $column->precision
        ];

        if ($column->options) {
            $mapping['options'] = $column->options;
        }

        if (isset($column->name)) {
            $mapping['columnName'] = $column->name;
        }

        if (isset($column->columnDefinition)) {
            $mapping['columnDefinition'] = $column->columnDefinition;
        }

        return $mapping;
    }
}
