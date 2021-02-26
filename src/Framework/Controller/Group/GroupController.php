<?php

namespace Survey54\Reap\Framework\Controller\Group;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\GroupService;

abstract class GroupController extends Controller
{
    protected GroupService $groupService;

    /**
     * GroupController constructor.
     * @param GroupService $groupService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(GroupService $groupService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->groupService = $groupService;
    }
}
