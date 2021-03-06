<?php
namespace Flowpack\Viewhelpertest\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Flowpack.Viewhelpertest".    *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Flow\Resource\ResourceManager;
use Flowpack\Viewhelpertest\Domain\Model\User;

/**
 * Viewhelpertest Form Controller
 */
class FormController extends AbstractBaseController {

	/**
	 * @Flow\Inject
	 * @var ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @param string $redirectAction
	 * @return void
	 */
	public function setupAction($redirectAction) {
		$this->createUsers();

		// This is needed in case the setup action is called with GET
		$this->persistenceManager->persistAll();

		$this->redirect($redirectAction);
	}

	/**
	 * @return void
	 */
	public function identityPropertiesAction() {
		$this->view->assign('user', $this->userRepository->findAll()->getFirst());
	}

	/**
	 * @param User $user
	 * @return void
	 */
	public function identityPropertiesValidateAction(User $user) {
		$this->userRepository->update($user);
		$this->addFlashMessage('Updated user "%s"', 'success', Message::SEVERITY_OK, array($user->getFirstName()));
		$this->redirect('identityProperties');
	}

	/**
	 * @return void
	 */
	public function nestedFormsAction() {
		$this->view->assign('user', $this->userRepository->findAll()->getFirst());
	}

	/**
	 * @param User $user
	 * @return void
	 */
	public function nestedFormsValidateAction(User $user) {
		$this->userRepository->update($user);
		$this->addFlashMessage('Updated user "%s"', 'success', Message::SEVERITY_OK, array($user->getFirstName()));
		$this->redirect('nestedForms');
	}

	/**
	 * @return void
	 */
	public function defaultValuesAction() {
		$this->view->assign('user', $this->userRepository->findAll()->getFirst());
	}

	/**
	 * @param User $user1
	 * @param User $user2
	 * @param User $user3
	 * @return void
	 */
	public function defaultValuesValidateAction(User $user1 = NULL, User $user2 = NULL, User $user3 = NULL) {
		if ($user1 !== NULL) {
			$user = $user1;
		} elseif ($user2 !== NULL) {
			$user = $user2;
		} else {
			$user = $user3;
		}
		if ($user === NULL) {
			$this->addFlashMessage('Missing user data', 'error', Message::SEVERITY_ERROR);
			$this->redirect('defaultValues');
		}
		if ($this->persistenceManager->isNewObject($user)) {
			$this->userRepository->add($user);
			$this->addFlashMessage('Added user "%s"', 'success', Message::SEVERITY_OK, array($user->getFirstName()));
		} else {
			$this->userRepository->update($user);
			$this->addFlashMessage('Updated user "%s"', 'success', Message::SEVERITY_OK, array($user->getFirstName()));
		}
		$this->redirect('defaultValues');
	}

	/**
	 * @param boolean $useDefaultResource
	 * @param boolean $useObjectAccessorMode
	 * @param User $user If specified that user will be displayed
	 * @return void
	 */
	public function uploadFormAction($useDefaultResource = FALSE, $useObjectAccessorMode = FALSE, User $user = NULL) {
		if ($user === NULL) {
			$user = $this->userRepository->findAll()->getFirst();
		}
		$this->view->assign('user', $user);
		$this->view->assign('useObjectAccessorMode', $useObjectAccessorMode);
		if ($useDefaultResource) {
			$this->view->assign('defaultResource', $this->getDummyResource());
		}
	}

	/**
	 * @param User $user
	 * @param boolean $useDefaultResource
	 * @param boolean $useObjectAccessorMode
	 * @return void
	 */
	public function uploadAction(User $user, $useDefaultResource = FALSE, $useObjectAccessorMode = FALSE) {
		// don't do this in productive code, use different action methods
		if ($this->persistenceManager->isNewObject($user)) {
			$this->userRepository->add($user);
			$this->addFlashMessage('Created user "%s"', 'success', Message::SEVERITY_OK, array($user->getFirstName()));
		} else {
			$this->userRepository->update($user);
			$this->addFlashMessage('Updated user "%s"', 'success', Message::SEVERITY_OK, array($user->getFirstName()));
		}
		$this->redirect('uploadForm', NULL, NULL, ['user' => $user, 'useDefaultResource' => $useDefaultResource, 'useObjectAccessorMode' => $useObjectAccessorMode]);
	}

	/**
	 * @return void
	 */
	protected function createUsers() {
		$this->userRepository->removeAll();
		$user = $this->createUser(1, 'John', 'Doe', TRUE, array('Flow', 'Neos', 'Music'), User::TITLE_MR);

		$this->createInvoice($user, 'invoice 01');
		$this->createInvoice($user, 'invoice 02');

		$this->userRepository->add($user);
	}

	/**
	 * @return Resource
	 */
	protected function getDummyResource() {
		$dummyFileContent = file_get_contents('resource://Flowpack.Viewhelpertest/Public/typo3_logo.png');
		$resource = $this->resourceManager->getResourceBySha1(sha1($dummyFileContent));
		if ($resource === NULL) {
			$resource = $this->resourceManager->importResourceFromContent($dummyFileContent, 'logo.png');
			$this->persistenceManager->whitelistObject($resource);
		}
		return $resource;
	}
}