<?php
/**
 * Only for J4 Support
 */

if (!class_exists('JObservableInterface')) {
    interface JObservableInterface
    {
        /**
         * Adds an observer to this JObservableInterface instance.
         * Ideally, this method should be called from the constructor of JObserverInterface
         * which should be instantiated by JObserverMapper.
         * The implementation of this function can use JObserverUpdater
         *
         * @param JObserverInterface $observer The observer to attach to $this observable subject
         *
         * @return  void
         *
         * @since   3.1.2
         */
        public function attachObserver(JObserverInterface $observer);
    }
}