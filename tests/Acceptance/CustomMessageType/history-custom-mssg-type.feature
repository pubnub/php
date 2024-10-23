
@featureSet=historyCustomMssgType @beta
Feature: History for VSP
  As a PubNub user I want to fetch history with message type.
  Client should be able to opt-out default `includeType`.

  Background:
    Given the demo keyset with enabled storage

  @contract=fetchHistoryWithPubNubMessageTypes
  Scenario: Client can fetch history with message types
    When I fetch message history for 'simple-channel' channel
    Then I receive a successful response
    And history response contains messages with '0' and '4' message types

  @contract=fetchHistoryWithUserAndPubNubTypes
  Scenario: Client can fetch history with customMessageType
    When I fetch message history for 'some-channel' channel
    Then I receive a successful response
    And history response contains messages with 'custom-message-type' and 'user-custom-message-type' types

  @contract=fetchHistoryWithoutTypes
  Scenario: Client can fetch history without customMessageType enabled by default
    When I fetch message history with 'include_custom_message_type' set to 'false' for 'some-channel' channel
    Then I receive a successful response
    And history response contains messages without customMessageType
