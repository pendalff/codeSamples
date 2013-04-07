<?php

class ClusterAggregatorCommandChain extends ClusterAggregatorCommandAbstract
{
  /**
   * @var IClusterAggregatorCommand[]
   */
  private $commands;

  /**
   * Return all chained specifications
   *
   * @return IClusterAggregatorCommand[]
   */
  public function getCommands()
  {
    return $this->commands;
  }

  /**
   * @param IClusterAggregatorCommand $command
   * @return ClusterAggregatorCommandChain
   */
  public function appendCommand(IClusterAggregatorCommand $command)
  {
    $this->commands[get_class($command)] = $command;
    return $this;
  }

  public function removeCommand($commandClassname)
  {
    if(array_key_exists($commandClassname, $this->commands)){
      unset($this->commands[$commandClassname]);
    }
  }

  /**
  * @param ClusterAggregatorContextCommand $context
  * @return ClusterAggregatorDataProvider|array
  * @throws CException
  */
  public function process( ClusterAggregatorContextCommand $context )
  {
    foreach($this->commands AS $command){
      $command->process($context);
    }

    return $context->getData();
  }

}
