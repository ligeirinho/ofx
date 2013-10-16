<?php

namespace Sinergia\Ofx;

use DateTime;

class Ofx
{
    public $headers;
    public $xml;

    public function __construct($file)
    {
        list($this->headers, $this->xml) = OfxParser::parse($file);
    }

    public function getTransactions()
    {
        $transactions = array();

        foreach ($this->xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN as $transaction) {
            $transactions[] = array(
                'type' => (string) $transaction->TRNTYPE,
                //'date' => DateTime::createFromFormat("YmdHis", substr($transaction->DTPOSTED, 0, 14)),
                'date' => substr($transaction->DTPOSTED, 0, 8), // Ymd
                'amount' => (float) $transaction->TRNAMT,
                'fit_id' => (string) $transaction->FITID,
                'check_number' => (string) $transaction->CHECKNUM,
                'ref_number' => (string) $transaction->REFNUM,
                'memo' => (string) $transaction->MEMO,
            );
        }

        return $transactions;
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
