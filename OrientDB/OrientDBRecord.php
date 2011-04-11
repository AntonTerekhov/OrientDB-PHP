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
        $this->recordID = $this->clusterID . ':' . $this->recordPos;
        // Parse record content
        $parser = new OrientDBRecordParser($this->content);

        $this->className = $parser->className;
        $this->data = $parser->data;
    }
}