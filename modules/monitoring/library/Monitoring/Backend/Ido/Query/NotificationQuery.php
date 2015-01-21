<?php

namespace Icinga\Module\Monitoring\Backend\Ido\Query;

class NotificationQuery extends IdoQuery
{
    protected $columnMap = array(
        'notification' => array(
            'notification_output'       => 'n.output',
            'notification_start_time'   => 'UNIX_TIMESTAMP(n.start_time)',
            'notification_state'        => 'n.state',
            'notification_object_id'    => 'n.object_id'
        ),
        'objects' => array(
            'host'                      => 'o.name1',
            'service'                   => 'o.name2'
        ),
        'contact' => array(
            'notification_contact'      => 'c_o.name1',
            'contact_object_id'         => 'c_o.object_id'
        ),
        'command' => array(
            'notification_command'      => 'cmd_o.name1'
        ),
        'acknowledgement' => array(
            'acknowledgement_entry_time'    => 'UNIX_TIMESTAMP(a.entry_time)',
            'acknowledgement_author_name'   => 'a.author_name',
            'acknowledgement_comment_data'  => 'a.comment_data'
        )
    );

    /**
     * Fetch basic information about notifications
     */
    protected function joinBaseTables()
    {
        $this->select->from(
            array('n' => $this->prefix . 'notifications'),
            array()
        );
        $this->joinedVirtualTables = array('notification' => true);
    }

    /**
     * Fetch description of each affected host/service
     */
    protected function joinObjects()
    {
        $this->select->join(
            array('o' => $this->prefix . 'objects'),
            'n.object_id = o.object_id AND o.is_active = 1 AND o.objecttype_id IN (1, 2)',
            array()
        );
    }

    /**
     * Fetch name of involved contacts and/or contact groups
     */
    protected function joinContact()
    {
        $this->select->join(
            array('c' => $this->prefix . 'contactnotifications'),
            'n.notification_id = c.notification_id',
            array()
        );
        $this->select->join(
            array('c_o' => $this->prefix . 'objects'),
            'c.contact_object_id = c_o.object_id',
            array()
        );
    }

    /**
     * Fetch name of the command which was used to send out a notification
     */
    protected function joinCommand()
    {
        $this->select->join(
            array('cmd_c' => $this->prefix . 'contactnotifications'),
            'n.notification_id = cmd_c.notification_id',
            array()
        );
        $this->select->joinLeft(
            array('cmd_m' => $this->prefix . 'contactnotificationmethods'),
            'cmd_c.contactnotification_id = cmd_m.contactnotification_id',
            array()
        );
        $this->select->joinLeft(
            array('cmd_o' => $this->prefix . 'objects'),
            'cmd_m.command_object_id = cmd_o.object_id',
            array()
        );
    }

    protected function joinAcknowledgement()
    {
        $this->select->joinLeft(
            array('a' => $this->prefix . 'acknowledgements'),
            'n.object_id = a.object_id',
            array()
        );
    }
}
