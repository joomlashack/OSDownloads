<?php
abstract class JFactory
{
    public static function getApplication()
    {
        return new class {
            public function getParams()
            {
                return new class {
                    public function get($param, $default = null)
                    {
                        if ('route_segment_files' === $param) {
                            return 'files';
                        }
                    }
                };
            }

            public function getMenu()
            {
                return new class {
                    public function getItem($itemId)
                    {
                        $item = null;

                        switch ($itemId) {
                            /**
                             *
                             * Single file
                             *
                             */
                            case 101:
                                $item = new class()
                                {
                                    public $component = 'com_osdownloads';

                                    public $query = [
                                        'view' => 'item',
                                        'id'   => 1,
                                    ];
                                };
                                break;

                            case 102:
                                $item = new class()
                                {
                                    public $component = 'com_osdownloads';

                                    public $query = [
                                        'view' => 'item',
                                        'id'   => 2,
                                    ];
                                };
                                break;

                            /**
                             *
                             * List of files
                             *
                             */
                            case 201:
                                $item = new class()
                                {
                                    public $component = 'com_osdownloads';

                                    public $query = [
                                        'view' => 'downloads',
                                        'id'   => 1,
                                    ];

                                };
                                break;

                            case 202:
                                $item = new class()
                                {
                                    public $component = 'com_osdownloads';

                                    public $query = [
                                        'view' => 'downloads',
                                        'id'   => 2,
                                    ];

                                };
                                break;

                            case 203:
                                $item = new class()
                                {
                                    public $component = 'com_osdownloads';

                                    public $query = [
                                        'view' => 'downloads',
                                        'id'   => 3,
                                    ];

                                };
                                break;
                        }

                        return $item;
                    }
                };
            }
        };
    }
}
