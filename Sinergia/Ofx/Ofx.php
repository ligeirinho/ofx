<?php

namespace Sinergia\Ofx;

class Ofx
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
                'type' => (string) $transaction->TRNTYPE,
                'date' => substr($transaction->DTPOSTED, 0, 8),
                'amount' => (float) $transaction->TRNAMT,
                'fit_id' => (string) $transaction->FITID,
                'check_number' => (string) $transaction->CHECKNUM,
                'ref_number' => (string) $transaction->REFNUM,
                'memo' => (string) $transaction->MEMO,
            );
            $id = implode("\t", array_merge($this->bank, $trans));
            $trans['id'] = sha1($id);
            $transactions[] = $trans;
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
