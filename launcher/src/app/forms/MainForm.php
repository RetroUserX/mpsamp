<?php
namespace app\forms;

use facade\Json;
use std, gui, framework, app;
use php\gui\UXDialog;

class MainForm extends AbstractForm
{
    
    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        if (!Regex::match('^.+:[0-9]{1,6}$', $this->editAlt->text))
        {
            UXDialog::show('Invalid server address!', 'ERROR');
            return;
        }
        global $servers;
        foreach($servers as $k => $v)
        {
            if ($v['address'] == $this->editAlt->text)
            {
                UXDialog::show('This server is already listed!', 'ERROR');
                return;
            }
        }
        $server = [
            'address' => $this->editAlt->text,
            'hostname' => 'Updating information... (' . $this->editAlt->text . ')',
            'players' => '0 / 0', 'ping' => '0',
            'gamemode' => '', 'language' => ''
        ];
        array_push($servers, $server);
        $this->module('MainModule')->loadMyServers();
        $data = Regex::split(':', $this->editAlt->text);
        $this->module('MainModule')->pingServer($data[0], $data[1]);
    }

    /**
     * @event tabPane.change 
     */
    function doTabPaneChange(UXEvent $e = null)
    {    
        if ($this->tabPane->selectedIndex != 0)
            $this->table->items->clear();
        else 
            $this->module('MainModule')->loadMyServers();
    }

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        $nickname = $this->edit->text;
        if (strlen($nickname) < 1 || strlen($nickname > 24))
        {
            UXDialog::showAndWait('Incorrect nickname!', 'ERROR');
            return;
        }
        if ($this->table->selectedIndex == -1)
        {
            UXDialog::showAndWait('Select any server!', 'ERROR');
            return;
        }
        $data = Regex::split(':', $this->table->focusedItem['address']);
        $this->table->selectedIndex = -1;
        if (!fs::exists('gta_sa.exe'))
        {
            UXDialog::showAndWait('gta_sa.exe not found!', 'ERROR');
            return;
        }
        execute('gta_sa.exe -multiplayer -n ' . $nickname . ' -h ' . $data[0] . ' -p ' . $data[1]);
        $this->iconified = true;
    }

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        global $servers;
        Json::toFile('slmp-settings.json', $servers);
    }
}
