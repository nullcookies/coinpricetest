<?php

namespace Retrinko\Ini\IniFile;

use Retrinko\Ini\Exceptions\FileException;
use Retrinko\Ini\Exceptions\InvalidDataException;
use Retrinko\Ini\IniFile;
use Retrinko\Ini\IniParser;
use Retrinko\Ini\IniSection;

class Factory
{
    /**
     * @param array $data
     *
     * @return IniFile
     * @throws InvalidDataException
     * @throws FileException
     */
    public static function fromArray(array $data)
    {
        $iniSections = IniParser::i()->parseArray($data);

        return self::fromIniSections($iniSections);
    }

    /**
     * @param IniSection[] $iniSections
     *
     * @return IniFile
     * @throws InvalidDataException
     * @throws FileException
     */
    public static function fromIniSections(array $iniSections)
    {
        $iniFile = new IniFile();
        foreach ($iniSections as $iniSection)
        {
            if (false === $iniSection instanceof IniSection)
            {
                throw new InvalidDataException('Invalid data! ');
            }
            $iniFile->addSection($iniSection);
        }

        return $iniFile;
    }

    /**
     * @param string $file File path
     *
     * @return IniFile
     * @throws FileException
     * @throws InvalidDataException
     */
    public static function fromFile($file)
    {
        return new IniFile($file);
    }

}