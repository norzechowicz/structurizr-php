<?php

declare(strict_types=1);

/*
 * This file is part of the Structurizr for PHP.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StructurizrPHP\StructurizrPHP\Core\View;

use StructurizrPHP\StructurizrPHP\Core\Model\Element;
use StructurizrPHP\StructurizrPHP\Core\Model\Model;
use StructurizrPHP\StructurizrPHP\Core\Model\SoftwareSystem;

abstract class View
{
    /**
     * @var SoftwareSystem
     */
    private $softwareSystem;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $key;

    /**
     * @var PaperSize|null
     */
    private $paperSize;

    /**
     * @var ViewSet
     */
    private $viewSet;

    /**
     * @var ElementView[]
     */
    private $elementViews;

    /**
     * @var RelationshipView[]
     */
    private $relationshipsViews;

    /**
     * @var AutomaticLayout|null
     */
    private $automaticLayout;

    public function __construct(
        SoftwareSystem $softwareSystem,
        string $title,
        string $description,
        string $key,
        ViewSet $viewSet
    ) {
        $this->softwareSystem = $softwareSystem;
        $this->title = $title;
        $this->description = $description;
        $this->key = $key;
        $this->viewSet = $viewSet;
        $this->elementViews = [];
        $this->relationshipsViews = [];
        $this->setAutomaticLayout(true);
    }

    protected function model() : Model
    {
        return $this->softwareSystem->model();
    }

    public function setPaperSize(PaperSize $paperSize) : void
    {
        $this->paperSize = $paperSize;
    }

    public function setAutomaticLayout(bool $enabled) : void
    {
        if ($enabled) {
            $this->automaticLayout = new AutomaticLayout(RankDirection::topBottom(), 300, 600, 200, false);
        } else {
            $this->automaticLayout = null;
        }
    }

    public function addElement(Element $element, bool $addRelationships) : void
    {
        $elementView = new ElementView($element);

        if ($addRelationships) {
            $this->addRelationships($element);
        }

        $this->elementViews[] = $elementView;
    }

    private function addRelationships(Element $element) : void
    {
        foreach ($element->relationships() as $relationship) {
            foreach ($this->elementViews as $elementView) {
                if ($elementView->element()->equals($relationship->destination())) {
                    $this->relationshipsViews[] = new RelationshipView($relationship);
                }
            }
        }

        foreach ($this->elementViews as $elementView) {
            foreach ($elementView->element()->relationships() as $r) {
                if ($r->destination()->equals($element)) {
                    $this->relationshipsViews[] = new RelationshipView($r);
                }
            }
        }
    }

    public function toArray() : array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'key' => $this->key,
            'softwareSystemId' => $this->softwareSystem->id(),
            'paperSize' => $this->paperSize ? $this->paperSize->size() : null,
            'automaticLayout' => $this->automaticLayout ? $this->automaticLayout->toArray() : null,
            'elements' => \array_map(
                function (ElementView $elementView) {
                    return $elementView->toArray();
                },
                $this->elementViews
            ),
            'relationships' => \array_map(
                function (RelationshipView $relationshipView) {
                    return $relationshipView->toArray();
                },
                $this->relationshipsViews
            ),
        ];
    }
}
