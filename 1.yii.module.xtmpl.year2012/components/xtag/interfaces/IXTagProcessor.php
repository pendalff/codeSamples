<?php
/**
 * Interface for parsers
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
interface IXTagProcessor
{
  /**
   * @return string current xmlns
   * @abstract
   */
  public function getXMLNamespace();

  /**
   * @return IViewRenderer
   */
  public function getViewRender();

  /**
   * @return array
   */
  public function getData();

  /**
   * @return \DOMDocument
   */
  public function getDoc();

  /**
   * @return \CBaseController
   */
  public function getContext();

  /**
   * Main process replace custom xml tags.
   * @abstract
   * @param IViewRenderer   $viewRender
   * @param array           $viewData
   * @param DOMDocument     $doc
   * @param CBaseController $context
   * @return DOMDocument
   */
  public function process(IViewRenderer $viewRender,
                          array            $viewData,
                          DOMDocument $doc,
                          CBaseController $context,
                          $file = null,
                          $counter = null
  );

  /**
   * @abstract
   * @return IXTagFactory
   */
  public function getFactory($file = null);
}
