<?php

namespace Sinergia\Ofx;

use Traversable;

class Ofx implements \IteratorAggregate, \Countable
{
    protected $headers;
    protected $xml;
    protected $bank = array();

    public function __construct($file)
    {
        list($this->headers, $this->xml) = OfxParser::parse($file);

        $bank = $this->xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM;
        $this->bank = array(
            'id' => (string) $bank->BANKID,
            'branch' => (string) $bank->BRANCHID,
            'account' => (string) $bank->ACCTID
        );
    }

    public function getServerDate()
    {
        return (string) $this->xml->BANKMSGSRSV1->SONRS->DTSERVER;
    }

    public function getBank()
    {
        return $this->bank;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getDateStart()
    {
        return substr($this->xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->DTSTART, 0, 8);
    }

    public function getDateEnd()
    {
        return substr($this->xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->DTEND, 0, 8);
    }

    public function getTransactions()
    {
        $transactions = array();

        foreach ($this->xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN as $transaction) {
            $trans = array(
                'type' => trim($transaction->TRNTYPE),
                'date' => substr($transaction->DTPOSTED, 0, 8),
                'amount' => (float) $transaction->TRNAMT,
                'fitid' => trim($transaction->FITID),
                'check_number' => trim($transaction->CHECKNUM),
                'ref_number' => trim($transaction->REFNUM),
                'memo' => trim($transaction->MEMO),
            );

            // ignore amount zero
            if ($trans['amount'] == 0) continue;

            $id = implode("\t", array_merge($this->bank, $trans));
            $id = sha1($id);
            $trans['id'] = $id;
            $transactions[$id] = $trans;
        }

        return $transactions;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->getTransactions();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->getTransactions());
    }
}

/*
 * <TRNTYPE>
<DTPOSTED
            <TRNAMT>-
<FITID>20
            <CHECKNUM
<REFNUM>8
<MEMO>

    attr_accessor :amount
    attr_accessor :amount_in_pennies
    attr_accessor :check_number
    attr_accessor :fit_id
    attr_accessor :memo
    attr_accessor :name
    attr_accessor :payee
    attr_accessor :posted_at
    attr_accessor :ref_number
    attr_accessor :type
    attr_accessor :sic
*/
