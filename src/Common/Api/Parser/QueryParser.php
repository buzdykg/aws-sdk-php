<?php
namespace Aws\Common\Api\Parser;

use Aws\Common\Api\Service;
use Aws\Result;
use GuzzleHttp\Command\Event\ProcessEvent;

/**
 * @internal Parses query (XML) responses (e.g., EC2, SQS, and many others)
 */
class QueryParser extends AbstractParser
{
    /** @var XmlParser */
    private $xmlParser;

    /** @var bool */
    private $honorResultWrapper;

    /**
     * @param Service   $api                Service description
     * @param XmlParser $xmlParser          Optional XML parser
     * @param bool      $honorResultWrapper Set to false to disable the peeling
     *                                      back of result wrappers from the
     *                                      output strucutre.
     */
    public function __construct(
        Service $api,
        XmlParser $xmlParser = null,
        $honorResultWrapper = true
    ) {
        parent::__construct($api);
        $this->xmlParser = $xmlParser ?: new XmlParser();
        $this->honorResultWrapper = $honorResultWrapper;
    }

    protected function createResult(Service $api, ProcessEvent $event)
    {
        $command = $event->getCommand();
        $output = $api->getOperation($command->getName())->getOutput();
        $xml = $event->getResponse()->xml();

        if ($this->honorResultWrapper && $output['resultWrapper']) {
            $xml = $xml->{$output['resultWrapper']};
        }

        return new Result($this->xmlParser->parse($output, $xml));
    }
}