<?php

interface PapayaDatabaseInterfaceRecord
  extends
    PapayaDatabaseInterfaceAccess,
    PapayaObjectInterfaceProperties,
    ArrayAccess,
    IteratorAggregate {

  function assign($data);

  function toArray();

  function load($filter);

  function save();

  function delete();
}

