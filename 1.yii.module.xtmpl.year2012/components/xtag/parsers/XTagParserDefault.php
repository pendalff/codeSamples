<?php
/**
 * Base of parser
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagParserDefault extends XTagParserBase implements IXTagParser
{

  protected function parse()
  {

    $tagName = $this->context->itemAt('tagName');
    $paramTagName = $this->context->itemAt('paramTagName');
    $recursiveParamTagName = $this->context->itemAt('recursiveParamTagName');
    $cleanHtml = $this->context->itemAt('clean_html') !== null ? $this->context->itemAt('clean_html') : true;

    $items = $this->getElements($tagName);

    foreach ($items AS $i => $node) {
      /**
       * @var DOMElement $node
       */
      $data = array();

      //set base props
      $data['type'] = $this->getType($node);
      $data['class'] = $this->getClassname($node);
      //el params
      $data['params'] =
          Arr::merge($this->getAttributes($node),
            $this->getParams($node, $paramTagName, $recursiveParamTagName));
      //inner html
      $data['html'] = $this->getHtml($node);

      if ($cleanHtml) {
        $data['html'] = preg_replace('/(<' . $paramTagName . '(.*)>).*(<\/' . $paramTagName . '>)/iUm', '', $data['html']);
        $data['html'] = trim($data['html']);
      }

      $data['node'] = $node;

      //add current node to transfer level
      $this->mapper->setCurrent($data);
      $this->onProcessParse(new CEvent($this, $this->mapper));
    }
  }

}
