<?php

class OrientDBCommandConfigList extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::CONFIG_LIST;
    }

    protected function parse()
    {
        $this->debugCommand('options_count');
        $numOptions = $this->readShort();

        $options = array();
        for ($i = 0; $i < $numOptions; $i++) {
            $this->debugCommand('option_name');
            $optionName = $this->readString();
            $this->debugCommand('option_value');
            $options[$optionName] = $this->readString();
        }

        return $options;
    }

}