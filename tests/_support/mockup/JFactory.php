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
        };
    }
}
