<?php

namespace GIFTploy\Filesystem;

/**
 * Interface for server entities.
 *
 * @author Patrik Chotěnovský
 */
interface ServerInterface
{

    public function getConfiguration();

    public function getAdapter();

}
