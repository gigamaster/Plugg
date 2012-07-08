<?php
require_once 'Auth/OpenID/Interface.php';

class Plugg_OpenIDAuth_OpenIDStore extends Auth_OpenID_OpenIDStore
{
    var $_db;
    var $_nonceLifetime;

    function __construct(Sabai_DB $db, $nonceLifetime = 3600)
    {
        $this->_db = $db;
        $this->_nonceLifetime = $nonceLifetime;
    }

    function storeAssociation($server_url, $association)
    {
        $sql = sprintf(
            'UPDATE %sassoc SET assoc_secret = %s, assoc_issued = %d, assoc_lifetime = %d, assoc_type = %s WHERE assoc_server_url = %s AND assoc_handle = %s',
            $this->_db->getResourcePrefix(),
            $this->_db->escapeBlob($association->secret),
            $association->issued,
            $association->lifetime,
            $this->_db->escapeString($association->assoc_type),
            $this->_db->escapeString($server_url),
            $this->_db->escapeString($association->handle)
        );
        if ($this->_db->exec($sql) > 0) return true;

        $sql = sprintf(
            'INSERT INTO %sassoc (assoc_server_url, assoc_handle, assoc_secret, assoc_issued, assoc_lifetime, assoc_type) VALUES (%s, %s, %s, %d, %d, %s)',
            $this->_db->getResourcePrefix(),
            $this->_db->escapeString($server_url),
            $this->_db->escapeString($association->handle),
            $this->_db->escapeBlob($association->secret),
            $association->issued,
            $association->lifetime,
            $this->_db->escapeString($association->assoc_type)
        );
        return $this->_db->exec($sql);
    }

    function cleanupNonces()
    {
        $sql = sprintf('DELETE FROM %snonce WHERE nonce_timestamp < %d', $this->_db->getResourcePrefix(), time() - $this->_nonceLifetime);
        return $this->_db->exec($sql, false);
    }

    function cleanupAssociations()
    {
        $sql = sprintf('DELETE FROM %sassoc WHERE issued + lifetime < %d', $this->_db->getResourcePrefix(), time());
        return $this->_db->exec($sql, false);
    }

    function getAssociation($server_url, $handle = null)
    {
        if (!empty($handle)) {
            $format = 'SELECT * FROM %1$sassoc WHERE assoc_server_url = %2$s AND assoc_handle = %3$s';
        } else {
            $format = 'SELECT * FROM %1$sassoc WHERE assoc_server_url = %2$s';
        }
        $format .= ' AND (assoc_issued + assoc_lifetime > %4$d) ORDER BY assoc_issued DESC';
        $sql = sprintf($format, $this->_db->getResourcePrefix(), $this->_db->escapeString($server_url), $this->_db->escapeString($handle), time());
        if (!$rs = $this->_db->query($sql, 1, 0)) return null;

        if (!$row = $rs->fetchAssoc()) return null;

        return new Auth_OpenID_Association($row['assoc_handle'], $row['assoc_secret'], $row['assoc_issued'], $row['assoc_lifetime'], $row['assoc_type']);
    }

    function removeAssociation($server_url, $handle)
    {
        $sql = sprintf(
            'DELETE FROM %sassoc WHERE assoc_server_url = %s AND assoc_handle = %s',
            $this->_db->getResourcePrefix(),
            $this->_db->escapeString($server_url),
            $this->_db->escapeString($handle)
        );
        return $this->_db->exec($sql, false);
    }

    function useNonce($server_url, $timestamp, $salt)
    {
        if (abs($timestamp - time()) > $this->_nonceLifetime) return false;

        $sql = sprintf(
            'SELECT COUNT(*) FROM %snonce WHERE nonce_server_url = %s AND nonce_timestamp = %d AND nonce_salt = %s',
            $this->_db->getResourcePrefix(),
            $this->_db->escapeString($server_url),
            $timestamp,
            $this->_db->escapeString($salt)
        );
        if (($rs = $this->_db->query($sql)) && $rs->fetchSingle() > 0) {
            return false;
        }
        $sql = sprintf(
            'INSERT INTO %snonce (nonce_server_url, nonce_timestamp, nonce_salt) VALUES (%s, %d, %s)',
            $this->_db->getResourcePrefix(),
            $this->_db->escapeString($server_url),
            $timestamp,
            $this->_db->escapeString($salt)
        );
        return $this->_db->exec($sql, false);
    }

    function reset()
    {
        $this->_db->exec(sprintf('DELETE FROM %sassoc', $this->_db->getResourcePrefix()));
        $this->_db->exec(sprintf('DELETE FROM %snonce', $this->_db->getResourcePrefix()));
    }
}