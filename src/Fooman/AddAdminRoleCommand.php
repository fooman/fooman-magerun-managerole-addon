<?php

namespace Fooman;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddAdminRoleCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:role:add')
            ->setDescription('Add a new admin role')
            ->addArgument('name', InputArgument::REQUIRED, 'Role Name');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            $output->writeln('Creating role');
            $name = $input->getArgument('name');
            $role = \Mage::getModel('admin/role')->load($name, 'role_name');
            if ($role->getId()) {
                throw new \Exception('Role already exists: ' . $name);
            }
            $role->setRoleName($name);
            $role->setRoleType('G');
            $role->setPid(false);
            $role->setTreeLevel(1);
            try {
                $role->save();
                //start with empty permissions only
                \Mage::getModel('admin/rules')
                    ->setRoleId($role->getId())
                    ->setResources(array(''))
                    ->saveRel();
            } catch (\Exception $e) {
                $output->writeln('Error creating role ' . $e->getMessage());
            }
            $output->writeln('Created role ' . $input->getArgument('name'));
        }
    }
}
