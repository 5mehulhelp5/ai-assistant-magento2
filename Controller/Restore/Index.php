<?php

declare(strict_types=1);

namespace MageCloud\AiAssistant\Controller\Restore;

use MageCloud\AiAssistant\Logger\Logger;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface;

class Index implements HttpGetActionInterface
{
    /**
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param Logger $logger
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param CheckoutSession $checkoutSession
     * @param LoggerInterface $psrLogger
     */
    public function __construct(
        private ResultFactory $resultFactory,
        private RequestInterface $request,
        private Logger $logger,
        private QuoteIdMaskFactory $quoteIdMaskFactory,
        private CartRepositoryInterface $cartRepository,
        private CheckoutSession $checkoutSession,
        private LoggerInterface $psrLogger
    ) {
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $cartId = (string) $this->request->getParam('cartId');

        if ($cartId === '') {
            $this->logger->warning('MageCloud_AiAssistant Restore: cartId parameter is missing or empty.');

            /** @var Forward $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $result->forward('noroute');

            return $result;
        }

        $this->logger->info('MageCloud_AiAssistant Restore: received cartId.', ['cartId' => $cartId]);

        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quoteId = $quoteIdMask->getQuoteId();

        if (!$quoteId) {
            $this->logger->warning(
                'MageCloud_AiAssistant Restore: no quote found for masked cartId.',
                ['cartId' => $cartId]
            );

            /** @var Forward $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $result->forward('noroute');

            return $result;
        }

        try {
            $quote = $this->cartRepository->get((int) $quoteId);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning(
                'MageCloud_AiAssistant Restore: quote not found.',
                ['quoteId' => $quoteId, 'cartId' => $cartId]
            );
            $this->psrLogger->error($e->getMessage(), ['exception' => $e]);

            /** @var Forward $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $result->forward('noroute');

            return $result;
        }

        if (!$quote->getIsActive()) {
            $this->logger->warning(
                'MageCloud_AiAssistant Restore: quote is not active.',
                ['quoteId' => $quoteId, 'cartId' => $cartId]
            );

            /** @var Forward $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $result->forward('noroute');

            return $result;
        }

        $this->checkoutSession->replaceQuote($quote);

        $this->logger->info(
            'MageCloud_AiAssistant Restore: quote loaded into session, redirecting to cart.',
            ['quoteId' => $quoteId]
        );

        /** @var Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setPath('checkout/cart');

        return $result;
    }
}