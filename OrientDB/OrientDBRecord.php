<?php

class OrientDBRecord
{

    /**
     * ClassID
     * @var int
     */
    public $classID;

    /**
     * ClassName as parsed from document
     * @var string
     */
    public $className;

    /**
     * Document type
     * @example OrientDB::RECORD_TYPE_DOCUMENT
     * @var string
     */
    public $type;

    /**
     * ClusterID
     * @var int
     */
    public $clusterID;

    /**
     * Record position in cluster
     * @var int
     */
    public $recordPos;

    /**
     * Full qualified record ID
     * @example 1:1
     * @var string
     */
    public $recordID;

    /**
     * Document version
     * @var int
     */
    public $version;

    /**
     * Document source as delivered from OrientDB
     * @var string
     */
    public $content;

    /**
     * A placeholder for document data
     * @var StdClass
     */
    public $data;

    /**
     * Parses $this->content and populates $this->data
     * @return void
     */
    public function parse()
    {
        // Form recordID
        $this->parseRecordID();
        // Parse record content
        $parser = new OrientDBRecordParser($this->content);

        $this->className = $parser->className;
        $this->data = $parser->data;
    }

    /**
     *
     * Parses recordID from $this->clusterID and $this->recordPos
     * @return void
     */
    private function parseRecordID()
    {
        if ((int) $this->clusterID !== $this->clusterID || (int) $this->recordPos !== $this->recordPos) {
            return;
        }
        if ($this->clusterID > 0 && $this->recordPos >= 0) {
            $this->recordID = $this->clusterID . ':' . $this->recordPos;
        }
    }
}