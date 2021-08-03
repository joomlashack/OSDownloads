<?php
/**
 * Only for J4 Support
 */
if (!class_exists('JObserverInterface')) {
    interface JObserverInterface
    {
        /**
         * Creates the associated observer instance and attaches it to the $observableObject
         *
         * @param   JObservableInterface  $observableObject  The observable subject object
         * @param   array                 $params            Params for this observer
         *
         * @return  JObserverInterface
         *
         * @since   3.1.2
         */
        public static function createObserver(JObservableInterface $observableObject, $params = array());
    }
}