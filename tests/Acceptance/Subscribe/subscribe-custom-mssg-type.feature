@featureSet=subscribeCustomMssgType @beta
Feature: Subscribe for VSP
  As a PubNub user I want to subscribe and receive custom message type.
  Client should be able to receive custom message type from subscribe response without any
  additional options set (like `include_custom_message_type`for other API).

  Background:
    Given the demo keyset

  @contract=subscribeReceiveMessagesWithTypes
  Scenario: Client can subscribe and receive messages with types
    When I subscribe to 'some-channel' channel
    Then I receive 2 messages in my subscribe response
    And response contains messages with 'custom-message-type' and 'user-custom-message-type' types
