<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_EmailAttachments
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\EmailAttachments\Observer;

/**
 * @magentoAppArea adminhtml
 */
class BeforeSendInvoiceCommentObserverTest extends Common
{
    /**
     * @magentoDataFixture   Magento/Sales/_files/invoice.php
     * @magentoConfigFixture current_store sales_email/invoice_comment/attachpdf 1
     */
    public function testWithAttachment()
    {
        $invoice = $this->sendInvoiceCommentEmail();
        $pdf = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('\Magento\Sales\Model\Order\Pdf\Invoice')->getPdf([$invoice]);
        $pdfAttachment = $this->getAttachmentOfType($this->getLastEmail(), 'application/pdf');
        $this->assertEquals(strlen($pdf->render()), strlen(base64_decode($pdfAttachment['Body'])));
    }

    /**
     * @magentoDataFixture   Magento/Sales/_files/invoice.php
     * @magentoDataFixture   Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoConfigFixture current_store sales_email/invoice_comment/attachagreement 1
     */
    public function testWithHtmlTermsAttachment()
    {
        $this->sendInvoiceCommentEmail();
        $termsAttachment = $this->getAttachmentOfType($this->getLastEmail(), 'text/html; charset=UTF-8');
        $this->assertContains('Checkout agreement content: <b>HTML</b>', base64_decode($termsAttachment['Body']));
    }

    /**
     * @magentoDataFixture   Magento/Sales/_files/invoice.php
     * @magentoDataFixture   Fooman/EmailAttachments/_files/agreement_active_with_text_content.php
     * @magentoConfigFixture current_store sales_email/invoice_comment/attachagreement 1
     */
    public function testWithTextTermsAttachment()
    {
        $this->sendInvoiceCommentEmail();
        $termsAttachment = $this->getAttachmentOfType($this->getLastEmail(), 'text/plain');
        $this->assertContains('Checkout agreement content: TEXT', base64_decode($termsAttachment['Body']));
    }


    /**
     * @magentoDataFixture   Magento/Sales/_files/invoice.php
     * @magentoConfigFixture current_store sales_email/invoice_comment/attachpdf 0
     */
    public function testWithoutAttachment()
    {
        $this->sendInvoiceCommentEmail();

        $pdfAttachment = $this->getAttachmentOfType($this->getLastEmail(), 'application/pdf');
        $this->assertFalse($pdfAttachment);
    }

    protected function getInvoice()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\ResourceModel\Order\Invoice\Collection'
        )->setPageSize(1);
        return $collection->getFirstItem();
    }

    /**
     * @return mixed
     */
    protected function sendInvoiceCommentEmail()
    {
        $invoice = $this->getInvoice();
        $invoiceSender = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender');

        $invoiceSender->send($invoice);
        return $invoice;
    }
}
