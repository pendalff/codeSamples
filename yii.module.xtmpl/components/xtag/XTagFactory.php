<?php
/**
 * Factories
 * @author: sem
 * Date: 27.03.12
 * Time: 14:39
 */
class XTagFactory extends CComponent implements IXTagFactory
{
  protected static $instances = array();


  protected static $renders  = null;

  protected static $builders = null;

  protected static $parsers  = null;

  /**
   * @var IXTagProcessor
   */
  protected $proc = null;

  protected $file = null;
  /**
   * @param IXTagProcessor $proc
   */
  public function __construct( IXTagProcessor & $proc, $file = null)
  {
    $this->proc = $proc;
    $this->file = $file;
    self::$instances[$file] = $this;
  }

  public static function getInstance( IXTagProcessor & $proc, $file = null ){
    if( null === self::$instances[$file]){
      new self( $proc, $file );
    }
    return self::$instances[$file];
  }


  /**
   * @param null|string $builderName
   * @return IXTagBuilder
   */
  public function builder( $builder = null )
  {
    Yii::import('xtmpl.components.xtag.builders.*');
    $builder = empty( $builder ) ? 'base' : $builder;

    /*
    if( !isset(self::$builders[$this->file][$builder]) )
    {
      $className= 'XTagBuilder'.ucfirst( $builder );

      if( !class_exists( $className ) )
      {
        throw new XTagFactoryException('Builder '.$builder.' with classname '.$className.' not found!');
      }

      self::$builders[$this->file][$builder] = new $className( $this->proc );
    }

    return self::$builders[$this->file][$builder];
    */

    $className= 'XTagBuilder'.ucfirst( $builder );

    if( !class_exists( $className ) )
    {
      throw new XTagFactoryException('Builder '.$builder.' with classname '.$className.' not found!');
    }

    return new $className( $this->proc );
  }

  /**
   * @param null|string $parserName
   * @return IXTagParser
   */
  public function parser ( $parser = null  )
  {
    Yii::import('xtmpl.components.xtag.parsers.*');
    $parser = empty( $parser ) ? 'base' : $parser;
    /*
    if( !isset(self::$parsers[$this->file][$parser]) )
    {
      $className= 'XTagParser'.ucfirst( $parser );

      if( !class_exists( $className ) )
      {
        throw new XTagFactoryException('Parser '.$parser.' with classname '.$className.' not found!');
      }

      self::$parsers[$this->file][$parser] = new $className( $this->proc );
    }

    return self::$parsers[$this->file][$parser];
    */
    $className= 'XTagParser'.ucfirst( $parser );

    if( !class_exists( $className ) )
    {
      throw new XTagFactoryException('Parser '.$parser.' with classname '.$className.' not found!');
    }

    return new $className( $this->proc );
  }

  /**
   * @param null|string $renderName
   * @return IXTagRender
   */
  public function render ( $render = null  )
  {
    Yii::import('xtmpl.components.xtag.renderers.*');
    $render = empty( $render ) ? 'base' : $render;
    /*
    if( !isset(self::$renders[$this->file][$render]) )
    {
      $className= 'XTagRender'.ucfirst( $render );
      if( !class_exists( $className ) )
      {
        throw new XTagFactoryException('Render '.$render.' with classname '.$className.' not found!');
      }
      self::$renders[$this->file][$render] = new $className( $this->proc );
    }

    return self::$renders[$this->file][$render];
    */
    $className= 'XTagRender'.ucfirst( $render );

    if( !class_exists( $className ) )
    {
      throw new XTagFactoryException('Render '.$render.' with classname '.$className.' not found!');
    }

    return new $className( $this->proc );
  }
}
