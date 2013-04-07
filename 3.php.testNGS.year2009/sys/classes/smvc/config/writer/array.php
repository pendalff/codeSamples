<?php
class SMVC_Config_Writer_Array extends SMVC_Config_Writer_FileAbstract
{
    /**
     * @return string
     */
    public function render()
    {
        $data        = $this->_config;
        $arrayString = "<?php\n"
                     . "return " . var_export($data, true) . ";\n";

        return $arrayString;
    }
}
