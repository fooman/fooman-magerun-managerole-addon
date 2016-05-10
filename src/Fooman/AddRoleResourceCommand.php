<?php

namespace Fooman;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddRoleResourceCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:role:resources-add')
            ->setDescription('Adds resources to an admin role')
            ->addArgument('name', InputArgument::REQUIRED, 'Role Name')
            ->addArgument('resources', InputArgument::REQUIRED, 'Resources, comma separated');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            $name = $input->getArgument('name');
            $role = \Mage::getModel('admin/role')->load($name, 'role_name');
            $permsToAdd = explode(',', $input->getArgument('resources'));
            if ($role->getId()) {
                try {
                    $newPerms = array();
                    $existingPerms = \Mage::getResourceModel('admin/rules_collection')
                        ->getByRoles($role->getId())
                        ->getResourcesPermissionsArray();

                    foreach ($existingPerms as $perm => $value) {
                        //Keep the existing permissons
                        if ($value == \Mage_Admin_Model_Rules::RULE_PERMISSION_ALLOWED) {
                            $newPerms[] = $perm;
                        }
                    }

                    foreach ($permsToAdd as $newPerm) {
                        //Make sure we can only add permissions that exist
                        if (isset($existingPerms[$newPerm])) {
                            $newPerms[] = $newPerm;
                        }
                    }

                    \Mage::getModel('admin/rules')
                        ->setRoleId($role->getId())
                        ->setResources($newPerms)
                        ->saveRel();

                } catch (\Exception $e) {
                    $output->writeln('Error updating resources ' . $e->getMessage());
                }
            }

            $output->writeln('Updated resources for ' . $input->getArgument('name'));

        }
    }
}
