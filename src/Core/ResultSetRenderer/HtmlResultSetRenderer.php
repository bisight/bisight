<?php

namespace BiSight\Core\ResultSetRenderer;

use BiSight\Core\Driver\ResultSetInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use BiSight\Core\Model\ColumnCollection;
use BiSight\Core\Utils\ExpressionUtils;

class HtmlResultSetRenderer
{
    public function render(ResultSetInterface $res, $offset = 0, $limit = null)
    {
        if (!$limit) {
            $limit = 10000; // default
        }
        $columns = $res->getColumns();

        $language = new ExpressionLanguage();
        $utils = new ExpressionUtils();

        $i = 0;
        $o = '';
        $o .= '<div class="table-responsive">';
        $o .= '<table class="table table-striped table-hover table-condensed">';
        $o .= '<thead><tr>';
        foreach ($columns as $column) {
            $o .= "<th";
            if ($column->getType() == 'money') {
                $o .= " style=\"text-align: right\"";
            }
            $o .= " class=\"column-type-" . $column->getType();
            if (!$column->isDefined()) {
                $o .= " column-undefined";
            }
            $o .= "\"";
            
            $o .= " title=\"" . $column->getName();
            
            if ($column->getDescription()) {
                $o .= ': ' . $column->getDescription();
            }
            $o .= ' [' . $column->getType() . ']';
            $o .= "\"";
            $o .= ">" . $column->getLabel() . "</th>";
            $i++;
        }
        $o .= '</tr></thead>';
        $i = 0;
        while ($row = $res->getRow()) {
            if ($i < $limit && $i >= $offset) {
                $o .= '<tr>';
                $rowData = array();
                foreach ($row as $key => $value) {
                    $rowData[$key]=(int)$value;
                }
                
                foreach ($row as $key => $value) {
                    $column = null;
                    foreach ($columns as $c) {
                        if ($c->getAlias() == $key) {
                            $column = $c;
                        }
                    }
                    
                    if ($column->isExpression()) {
                        $rowData['utils'] = $utils;
                        
                        $value = $language->evaluate($column->getExpression(), $rowData);
                        //print_r($rowData); echo $column->getExpression();
                        //echo "VALUE: " . $value;
                    }

                    $o .= "<td";

                    if ($column->getType() == 'money') {
                        $o .= " style=\"text-align: right\"";
                    }
                    
                    $o .= " class=\"column-type-" . $column->getType();
                    if (!$column->isDefined()) {
                        $o .= " column-undefined";
                    }
                    $o .= "\"";


                    $o .= ">";

                    if ($column->getType() == 'money') {
                        $o .= "&euro; ";
                        if ($value=='') {
                            $value = 0.00;
                        }
                    }

                    $o .= nl2br($value);
                    $o .= "</td>";
                }
                $o .= '</tr>' . "\n";
            }
            //exit();
            $i++;
        }

        $o .= '</table>';
        $o .= '</div>';
        return $o;
    }
}
