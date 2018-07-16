<?php
namespace PharIo\Phive;

class DirectUrlResolver extends AbstractRequestedPharResolver {

    /** @var HttpClient */
    private $httpClient;

    /**
     * DirectUrlResolver constructor.
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient) {
        $this->httpClient = $httpClient;
    }

    /**
     * @param RequestedPhar $requestedPhar
     *
     * @return SourceRepository
     */
    public function resolve(RequestedPhar $requestedPhar) {
        if (!$requestedPhar->hasUrl()) {
            return $this->tryNext($requestedPhar);
        }

        $url = $requestedPhar->getUrl();
        $result = $this->httpClient->head($url);
        if ($result->isNotFound()) {
            return new UrlRepository();
        }

        $sigUrl = new Url($url->asString() . '.asc');
        $sigResult = $this->httpClient->head($sigUrl);

        if ($sigResult->isNotFound()) {
            return new UrlRepository($url);
        }

        return new UrlRepository($url, $sigUrl);
    }
}
