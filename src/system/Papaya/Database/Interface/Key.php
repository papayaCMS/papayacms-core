<?php

interface PapayaDatabaseInterfaceKey {

  const DATABASE_PROVIDED = 1;
  const CLIENT_GENERATED = 2;

  const ACTION_FILTER = 1;
  const ACTION_CREATE = 2;

  function clear();

  function assign(array $data);

  function getProperties();

  function getFilter($for = self::ACTION_FILTER);

  function getQualities();

  function exists();

  function __toString();
}