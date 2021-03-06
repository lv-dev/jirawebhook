<?php
/**
 * Author: Elena Kolevska
 */
namespace JiraWebhook\Tests;

use PHPUnit_Framework_TestCase;
use JiraWebhook\Models\JiraUser;
use JiraWebhook\Models\JiraIssueComment;
use JiraWebhook\Exceptions\JiraWebhookDataException;
use JiraWebhook\Tests\Factories\JiraWebhookPayloadFactory;

/**
 * @property  array issueCommentData
 */
class JiraIssueCommentTest extends PHPUnit_Framework_TestCase {

    private $issueCommentData;

    public function setUp()
    {
        $payload = JiraWebhookPayloadFactory::create();
        $this->issueCommentData = $payload['issue']['fields']['comment']['comments'][0];
    }

    public function testParse()
    {
        $issueComment = JiraIssueComment::parse($this->issueCommentData);

        $this->assertEquals($this->issueCommentData['self'], $issueComment->getSelf());
        $this->assertEquals($this->issueCommentData['id'], $issueComment->getId());
        $this->assertEquals($this->issueCommentData['body'], $issueComment->getBody());
        $this->assertEquals($this->issueCommentData['created'], $issueComment->getCreated());
        $this->assertEquals($this->issueCommentData['updated'], $issueComment->getUpdated());
        $this->assertInstanceOf(JiraUser::class, $issueComment->getAuthor());
        $this->assertInstanceOf(JiraUser::class, $issueComment->getUpdateAuthor());
    }

    public function testExceptionIsThrownWhenCommentAuthorIsntSet()
    {
        $this->issueCommentData['author'] = null;
        $this->expectException(JiraWebhookDataException::class);
        JiraIssueComment::parse($this->issueCommentData);
    }
    public function testExceptionIsThrownWhenCommentBodyIsntSet()
    {
        $this->issueCommentData['body'] = null;
        $this->expectException(JiraWebhookDataException::class);
        JiraIssueComment::parse($this->issueCommentData);
    }

    public function testMentionedUsersParsing()
    {
        $jiraIssueComment = new JiraIssueComment();
        $jiraIssueComment->setBody("[~foogirl]foobar[~barboy]");
        $usernames = $jiraIssueComment->getMentionedUsersNicknames();

        $this->assertCount(2, $usernames);
        $this->assertArraySubset(['foogirl','barboy'], $usernames);
    }
}