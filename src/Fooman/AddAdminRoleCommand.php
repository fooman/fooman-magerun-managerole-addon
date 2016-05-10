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
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            $output->writeln('Creating role');
            $role = \Mage::getModel('admin/role');
            $role->setRoleName($input->getArgument('name'));
            $role->setRoleType('G');
            $role->setPid(false);
            try {
                $role->save();
            } catch (\Exception $e) {
                $output->writeln('Error creating role ' . $e->getMessage());

            }
            $output->writeln('Created role ' . $input->getArgument('name'));

        }
    }
}
