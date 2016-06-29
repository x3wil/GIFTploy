<?php

namespace GIFTploy\Filesystem;

/**
 * Interface for server entities.
 *
 * @author Patrik Chotěnovský
 */
interface ServerInterface
{

    public function getId();

    public function getConfiguration();

    public function getAdapter();

    public function getType();

}
