<?php

namespace Freescout\SafeEmailNotifications\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\SafeEmailNotifications\SubjectBuilder;
use Modules\SafeEmailNotifications\SubjectResult;

/**
 * Stub classes so method_exists() passes and SubjectBuilder can call getFullName, getCreatedBy, etc.
 * Written for PHP 7.4 (no constructor property promotion).
 */
final class PersonStub
{
    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getFullName(bool $x = false): string
    {
        return $this->name;
    }
}

final class ThreadStub
{
    /** @var int */
    public $type;
    /** @var int|null */
    public $action_type;
    /** @var \DateTimeInterface */
    public $created_at;
    /** @var string */
    public $createdByName;
    /** @var string */
    public $statusName;
    /** @var string */
    public $assigneeName;

    public function __construct(
        int $type,
        ?int $action_type,
        \DateTimeInterface $created_at,
        string $createdByName = 'Jane Doe',
        string $statusName = 'active',
        string $assigneeName = 'John Smith'
    ) {
        $this->type = $type;
        $this->action_type = $action_type;
        $this->created_at = $created_at;
        $this->createdByName = $createdByName;
        $this->statusName = $statusName;
        $this->assigneeName = $assigneeName;
    }

    public function getCreatedBy(): PersonStub
    {
        return new PersonStub($this->createdByName);
    }

    public function getStatusName(): string
    {
        return $this->statusName;
    }

    public function getAssigneeName(bool $a, $b): string
    {
        return $this->assigneeName;
    }
}

class SubjectBuilderTest extends TestCase
{
    private SubjectBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new SubjectBuilder();
    }

    private function createConversation(array $threads, ?object $customer = null): object
    {
        $conv = new \stdClass();
        $conv->number = '42';
        $conv->threads = $threads;
        $conv->customer = $customer;
        return $conv;
    }

    private function createThread(int $type, ?int $actionType = null, ?string $createdAt = null, string $createdByName = 'Jane Doe', string $statusName = 'active', string $assigneeName = 'John Smith'): object
    {
        return new ThreadStub(
            $type,
            $actionType,
            $createdAt ? \DateTime::createFromFormat('Y-m-d H:i:s', $createdAt) : new \DateTime(),
            $createdByName,
            $statusName,
            $assigneeName,
        );
    }

    /** @test */
    public function it_returns_null_for_null_conversation(): void
    {
        self::assertNull($this->builder->build(null));
    }

    /** @test */
    public function it_returns_null_for_empty_conversation_with_no_threads(): void
    {
        $conv = $this->createConversation([]);
        self::assertNull($this->builder->build($conv));
    }

    /** @test */
    public function it_builds_subject_for_new_conversation(): void
    {
        $customer = new PersonStub('Jane Smith');
        $thread = $this->createThread(SubjectBuilder::TYPE_CUSTOMER);
        $conv = $this->createConversation([$thread], $customer);
        $result = $this->builder->build($conv);
        self::assertInstanceOf(SubjectResult::class, $result);
        self::assertSame('[#42] ', $result->prefix);
        self::assertSame('requesting_assistance', $result->key);
        self::assertSame(['name' => 'Jane Smith'], $result->replace);
    }

    /** @test */
    public function it_builds_subject_for_customer_reply(): void
    {
        $customer = new PersonStub('Jane Smith');
        $thread = $this->createThread(SubjectBuilder::TYPE_CUSTOMER);
        $other = new ThreadStub(SubjectBuilder::TYPE_CUSTOMER, null, new \DateTime('-1 hour'), 'Anyone', 'active', 'Anyone');
        $conv = $this->createConversation([$other, $thread], $customer);
        $result = $this->builder->build($conv);
        self::assertInstanceOf(SubjectResult::class, $result);
        self::assertSame('replied', $result->key);
        self::assertSame(['name' => 'Jane Smith'], $result->replace);
    }

    /** @test */
    public function it_builds_subject_for_note_added(): void
    {
        $thread = $this->createThread(SubjectBuilder::TYPE_NOTE);
        $conv = $this->createConversation([$thread]);
        $result = $this->builder->build($conv);
        self::assertInstanceOf(SubjectResult::class, $result);
        self::assertSame('note_added', $result->key);
        self::assertSame(['person' => 'Jane Doe'], $result->replace);
    }

    /** @test */
    public function it_builds_subject_for_user_reply(): void
    {
        $thread = $this->createThread(SubjectBuilder::TYPE_MESSAGE);
        $conv = $this->createConversation([$thread]);
        $result = $this->builder->build($conv);
        self::assertInstanceOf(SubjectResult::class, $result);
        self::assertSame('replied', $result->key);
        self::assertSame(['name' => 'Jane Doe'], $result->replace);
    }

    /** @test */
    public function it_builds_subject_for_status_changed(): void
    {
        $thread = $this->createThread(SubjectBuilder::TYPE_LINEITEM, SubjectBuilder::ACTION_TYPE_STATUS_CHANGED);
        $conv = $this->createConversation([$thread]);
        $result = $this->builder->build($conv);
        self::assertInstanceOf(SubjectResult::class, $result);
        self::assertSame('status_changed', $result->key);
        self::assertSame(['person' => 'Jane Doe', 'status' => 'active'], $result->replace);
    }

    /** @test */
    public function it_builds_subject_for_assigned(): void
    {
        $thread = $this->createThread(SubjectBuilder::TYPE_LINEITEM, SubjectBuilder::ACTION_TYPE_USER_CHANGED);
        $conv = $this->createConversation([$thread]);
        $result = $this->builder->build($conv);
        self::assertInstanceOf(SubjectResult::class, $result);
        self::assertSame('assigned', $result->key);
        self::assertSame(['person' => 'John Smith'], $result->replace);
    }

    /** @test */
    public function it_returns_null_for_unknown_line_item_action(): void
    {
        $thread = $this->createThread(SubjectBuilder::TYPE_LINEITEM, 99);
        $conv = $this->createConversation([$thread]);
        self::assertNull($this->builder->build($conv));
    }

    /** @test */
    public function subject_result_formats_with_translator(): void
    {
        $result = new SubjectResult('[#1] ', 'requesting_assistance', ['name' => 'Tim']);
        $translator = fn($key, $replace) => $replace['name'] . ' is requesting assistance';
        self::assertSame('[#1] Tim is requesting assistance', $result->format($translator));
    }
}
