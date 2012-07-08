<?php
interface Plugg_User_Manager
{
    function userFetchIdentitiesByIds($userIds);
    function userFetchIdentitiesSortbyId($limit, $offset, $order);
    function userFetchIdentitiesSortbyUsername($limit, $offset, $order);
    function userFetchIdentitiesSortbyName($limit, $offset, $order);
    function userFetchIdentitiesSortbyEmail($limit, $offset, $order);
    function userFetchIdentitiesSortbyUrl($limit, $offset, $order);
    function userFetchIdentitiesSortbyTimestamp($limit, $offset, $order);
    function userFetchIdentityByUsername($userName);
    function userFetchIdentityByEmail($email);
    function userCountIdentities();
    function userGetRoleIdsById($userId);
    function userGetIdentityPasswordById($userId);
    function userGetCurrentUser();
    function userGetAnonymousIdentity();
    function userGetManagerSettings();
}