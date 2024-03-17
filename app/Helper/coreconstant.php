<?php

const API_ACCESS_KEY=1;
// User Role Type

const USER_ROLE_ADMIN = 1;
const USER_ROLE_USER = 2;

// ENABLE / DISABLE
const DISABLE = 0;
const ENABLE = 1;


// Status
const STATUS_PENDING = 0;
const STATUS_ACCEPTED = 1;
const STATUS_REJECTED = 2;
const STATUS_SUCCESS = 1;
const STATUS_SUSPENDED = 4;
const STATUS_DELETED = 5;
const STATUS_ALL = 6;
const STATUS_FORCE_DELETED = 7;
const STATUS_USER_DEACTIVATE = 8;

const STATUS_ACTIVE = 1;
const STATUS_DEACTIVE = 0;

const BTC = 1;
const CARD = 2;
const PAYPAL = 3;
const BANK_DEPOSIT = 4;
const STRIPE = 5;
const WALLET_DEPOSIT = 6;
const CRYPTO = 8; // make 8 for ico
const SKRILL = 11;
const PAYSTACK = 9;
const RAZORPAY = 10;


const  SEND_FEES_FIXED  = 1;
const  SEND_FEES_PERCENTAGE  = 2;

//Varification send Type
const Mail = 1;
const PHONE = 2;


const IOS = 1;
const ANDROIND = 2;

// User Activity
const ADDRESS_TYPE_EXTERNAL = 1;
const ADDRESS_TYPE_INTERNAL = 2;

const IMG_PATH = 'uploaded_file/uploads/';
const IMG_VIEW_PATH = 'uploaded_file/uploads/';

const IMG_OTHER_PATH = 'uploaded_file/others/';
const IMG_USER_PATH = 'uploaded_file/users/';
const IMG_ICON_PATH = 'uploaded_file/uploads/coin/';
const IMG_SLEEP_PATH = 'uploaded_file/sleep/';
const IMG_USER_VIEW_PATH = 'uploaded_file/users/';
const IMG_SLEEP_VIEW_PATH = 'uploaded_file/sleep/';
const IMG_USER_VERIFICATION_PATH = 'users/verifications/';

// Gift Card Banner Image Path
const GIFT_CARD_BANNER = 'uploaded_file/gift_card_banner/';

const DISCOUNT_TYPE_FIXED = 1;
const DISCOUNT_TYPE_PERCENTAGE = 2;

const DEPOSIT = 1;
const WITHDRAWAL = 2;

const PAYMENT_TYPE_BTC = 1;
const PAYMENT_TYPE_USD = 2;
const PAYMENT_TYPE_ETH = 3;
const PAYMENT_TYPE_LTC = 4;
const PAYMENT_TYPE_LTCT = 5;
const PAYMENT_TYPE_DOGE = 6;
const PAYMENT_TYPE_BCH = 7;
const PAYMENT_TYPE_DASH = 8;
const PAYMENT_TYPE_USDT = 9;
// plan bonus
const PLAN_BONUS_TYPE_FIXED = 1;
const PLAN_BONUS_TYPE_PERCENTAGE = 2;

//
const CREDIT = 1;
const DEBIT = 2;

//User Activity
const USER_ACTIVITY_LOGIN=1;
const USER_ACTIVITY_MOVE_COIN=2;
const USER_ACTIVITY_WITHDRAWAL=3;
const USER_ACTIVITY_CREATE_WALLET=4;
const USER_ACTIVITY_CREATE_ADDRESS=5;
const USER_ACTIVITY_MAKE_PRIMARY_WALLET=6;
const USER_ACTIVITY_PROFILE_IMAGE_UPLOAD=7;
const USER_ACTIVITY_UPDATE_PASSWORD=8;
const USER_ACTIVITY_UPDATE_EMAIL=12;
const USER_ACTIVITY_ACTIVE=9;
const USER_ACTIVITY_HALF_ACTIVE=10;
const USER_ACTIVITY_INACTIVE=11;
const USER_ACTIVITY_LOGOUT=12;
const USER_ACTIVITY_PROFILE_UPDATE=13;

const DEFAULT_COIN_TYPE="Default";

//wallet types
const PERSONAL_WALLET = 1;
const CO_WALLET = 2;
const COMMAND_TYPE_CACHE = 1;
const COMMAND_TYPE_CONFIG = 2;
const COMMAND_TYPE_ROUTE = 3;
const COMMAND_TYPE_VIEW = 4;
const COMMAND_TYPE_WALLET = 5;
const COMMAND_TYPE_TRADE_FEES = 6;
const COMMAND_TYPE_MIGRATE = 7;
const COMMAND_TYPE_COIN_PAIR = 8;
const COMMAND_TYPE_TOKEN_DEPOSIT = 10;
const COMMAND_TYPE_ADJUST_TOKEN_DEPOSIT = 11;
const COMMAND_TYPE_ERC20_TOKEN_DEPOSIT = 12;
const COMMAND_TYPE_SCHEDULE_START = 13;
const COMMAND_TYPE_DELETE_BUY_ORDER = 14;
const COMMAND_TYPE_DELETE_SELL_ORDER = 15;
const COMMAND_TYPE_DELETE_TRANSACTION = 16;
const COMMAND_TYPE_DELETE_CHART = 17;
const COMMAND_TYPE_DELETE_FAILED_JOBS = 27;


//co wallet feature's admin settings
const CO_WALLET_FEATURE_ACTIVE_SLUG = 'co_wallet_feature_active';
const MAX_CO_WALLET_USER_SLUG = 'max_co_wallet_user';
const CO_WALLET_WITHDRAWAL_USER_APPROVAL_PERCENTAGE_SLUG = 'co_wallet_withdrawal_user_approval_percentage';

const CHECK_STATUS = 1;
const CHECK_WITHDRAWAL_STATUS = 2;
const CHECK_WITHDRAWAL_FEES = 3;
const CHECK_MINIMUM_WITHDRAWAL = 4;
const CHECK_MAXIMUM_WITHDRAWAL = 5;

const NOT_DEFINED = 0;
const COIN_PAYMENT = 1;
const BITCOIN_API = 2;
const BITGO_API = 3;
const ERC20_TOKEN = 4;
const BEP20_TOKEN = 5;
const TRC20_TOKEN = 6;
const FIAT_CURREMCY = 7;

const ORDER_TYPE_BUY = 'buy';
const ORDER_TYPE_SELL = 'sell';
const ORDER_TYPE_BUY_SELL = 'buy_sell';

const DASHBOARD_TYPE = 'dashboard';

const GENDER_MALE = 1;
const GENDER_FEMALE = 2;
const GENDER_OTHERS = 3;

const CODE_TYPE_EMAIL = 1;
const CODE_TYPE_PHONE = 2;

const KYC_NOT_SUBMITTED = '';
const KYC_PENDING = 0;
const KYC_APPROVED = 1;
const KYC_REJECTED = 2;

const KYC_NID_REQUIRED = 1;
const KYC_PASSPORT_REQUIRED = 2;
const KYC_DRIVING_REQUIRED = 3;

const PAGE_TYPE_PRODUCT = 1;
const PAGE_TYPE_SERVICE = 2;
const PAGE_TYPE_SUPPORT = 3;

const STRONG_KEY = 'aBf5HIk4aD2lW0Q';

const USDT_OMNILAYER = 'USDT';
const USDT_SOLANA = 'USDT.SOL';
const USDT_TRC20 = 'USDT.TRC20';
const USDT_ERC20 = 'USDT.ERC20';
const USDT_BEP20 = 'USDT.BEP20';

const COIN_USDT = 'USDT';

//faq type
const FAQ_TYPE_MAIN = 1;
const FAQ_TYPE_DEPOSIT = 2;
const FAQ_TYPE_WITHDRAWN = 3;
const FAQ_TYPE_BUY = 4;
const FAQ_TYPE_SELL = 5;
const FAQ_TYPE_COIN = 6;
const FAQ_TYPE_WALLET = 7;
const FAQ_TYPE_TRADE = 8;
const FAQ_TYPE_FIAT_DEPOSIT = 9;
const FAQ_TYPE_P2P = 10;
const FAQ_TYPE_STACKING = 11;
const FAQ_TYPE_GIFT_CARD = 12;

//progress status type
const PROGRESS_STATUS_TYPE_DEPOSIT = 1;
const PROGRESS_STATUS_TYPE_WITHDRAWN = 2;

//kyc list
const KYC_PHONE_VERIFICATION = 1;
const KYC_EMAIL_VERIFICATION = 2;
const KYC_NID_VERIFICATION = 3;
const KYC_PASSPORT_VERIFICATION = 4;
const KYC_DRIVING_VERIFICATION = 5;
const KYC_VOTERS_CARD_VERIFICATION = 6;

//exchange page layout view
const EXCHANGE_LAYOUT_ONE = 1;
const EXCHANGE_LAYOUT_TWO = 2;

// Two Factor Authentication
const GOOGLE_AUTH = 1;
const EMAIL_AUTH = 2;
const PHONE_AUTH = 3;

//status online
const STATUS_OFF_ONLINE = 0;
const STATUS_ONLINE = 1;

// custom page link type
const CUSTOM_PAGE_LINK_PAGE = 1;
const CUSTOM_PAGE_LINK_URL = 2;

//webhook type
const WEBHOOK_TYPE_TRANSFER = 'transfer';
const WEBHOOK_TYPE_TRANSACTION = 'transaction';
const WEBHOOK_TYPE_PENDING_APPROVAL = 'pendingapproval';
const WEBHOOK_TYPE_ADDRESS_CONFIRM = 'address_confirmation';
const WEBHOOK_TYPE_LOW_FEE = 'lowFee';

// check deposit
const CHECK_DEPOSIT = 1;
const ADJUST_DEPOSIT = 2;

//  Fiat Withdraw type
const Fiat_Withdraw_PERCENT = 1;
const Fiat_Withdraw_FIXED = 2;

//display link or text
const SHOW_LINK = 1;
const SHOW_TEXT = 2;

// estimate fee
const TRC20ESTFEE = 15;
const ENERGYSUN = 420;

//token receive type
const TYPE_DEPOSIT=1;
const TYPE_BUY=2;

//captcha
const CAPTCHA_TYPE_DISABLE = 0;
const CAPTCHA_TYPE_RECAPTCHA = 1;
const CAPTCHA_TYPE_GEETESTCAPTCHA = 2;

//kyc type
const KYC_TYPE_DISABLE = 0;
const KYC_TYPE_MANUAL = 1;
const KYC_TYPE_PERSONA = 2;

//referral type
const REFERRAL_TYPE_WITHDRAWAL = 1;
const REFERRAL_TYPE_TRADE = 2;

//email template number
const EMAIL_TEMPLATE_NUMBER_ONE = 1;
const EMAIL_TEMPLATE_NUMBER_TWO = 2;
const EMAIL_TEMPLATE_NUMBER_THREE = 3;
const EMAIL_TEMPLATE_NUMBER_FOUR =4 ;

//sms type
const SMS_TYPE_DISABLE = 0;
const SMS_TYPE_TWILIO = 1;
const SMS_TYPE_NEXMO = 2;
const SMS_TYPE_AFRICA_TALK = 3;

//staking terms type
const STAKING_TERMS_TYPE_STRICT = 1;
const STAKING_TERMS_TYPE_FLEXIBLE = 2;

//staking investment status
const STAKING_INVESTMENT_STATUS_RUNNING = 1;
const STAKING_INVESTMENT_STATUS_CANCELED = 2;
const STAKING_INVESTMENT_STATUS_UNPAID = 3;
const STAKING_INVESTMENT_STATUS_PAID = 4;
const STAKING_INVESTMENT_STATUS_SUCCESS = 5;

//auto renew
const STAKING_IS_NOT_AUTO_RENEW = 1;
const STAKING_IS_AUTO_RENEW = 2;

//user profile delete request status
const USER_DELETE_REQUEST_STATUS_PENDING = 1;
const USER_DELETE_REQUEST_STATUS_ACCEPTED = 2;
const USER_DELETE_REQUEST_STATUS_REJECTED = 3;

//wallet type
const SPOT_TRADE = 1;
const FUTURE_TRADE = 2;

//trade order type
const LIMIT_ORDER = 1;
const MARKET_ORDER = 2;
const STOP_LIMIT_ORDER = 3;
const STOP_MARKET_ORDER = 4;


//buy sell order status
const RUNNING_ORDER = 0;
const EXECUTE_ORDER = 1;
const OPEN_ORDER = 2;
const CANCEL_ORDER = 3;
const CLOSED_ORDER = 4;

//margin mode
const MARGIN_MODE_ISOLATED = 1;
const MARGIN_MODE_CROSS = 2;

//future trade type
const FUTURE_TRADE_PERPETUAL = 1;
const FUTURE_TRADE_DELIVERY = 1;

//future trade position type
const FUTURE_TRADE_IS_NOT_POSITION = 0;
const FUTURE_TRADE_IS_POSITION = 1;
const FUTURE_TRADE_HOLD_POSITION = 2;
const FUTURE_TRADE_STOP_MARKET_LIMIT_POSITION = 3;

//trade side type buy sell
const TRADE_TYPE_BUY = 1;
const TRADE_TYPE_SELL = 2;

//future trade coin type
const FUTURE_TRADE_COIN_TYPE_USDM = 1;
const FUTURE_TRADE_COIN_TYPE_COINM = 2;

//trade type
const FUTURE_TRADE_TYPE_OPEN = 1;
const FUTURE_TRADE_TYPE_CLOSE = 2;
const FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE = 3;
const FUTURE_TRADE_TYPE_STOP_LOSS_CLOSE = 4;
const FUTURE_TRADE_TYPE_STOP_MARKET_LIMIT_CLOSE = 5;

//amount type
const AMOUNT_TYPE_BASE = 1;
const AMOUNT_TYPE_TRADE = 2;


// trigger condition
const TRIGGER_CONDITION_LAST=1;
const TRIGGER_CONDITION_MARKET=2;

const FUTURE_TRADE_TRANSACTION_TYPE_TRANSFER = 1;
const FUTURE_TRADE_TRANSACTION_TYPE_COMMISSION = 2;
const FUTURE_TRADE_TRANSACTION_TYPE_FUNDING_FEES = 3;
const FUTURE_TRADE_TRANSACTION_TYPE_REALIZED_PNL = 4;



// Gift Card Wallet Type
const EXCHANGE_WALLET_TYPE = 1;
const P2P_WALLET_TYPE = 2;

// Gift Card Status
const GIFT_CARD_STATUS_ACTIVE = 1;
const GIFT_CARD_STATUS_REDEEMED = 2;
const GIFT_CARD_STATUS_TRANSFARED = 3;
const GIFT_CARD_STATUS_TRADING = 4;
const GIFT_CARD_STATUS_LOCKED = 5;


// Gift Card Buy Request Status
const GIFT_CARD_BULK_BUY = 1;
const GIFT_CARD_SINGLE_BUY = 0;

// Send Gift Card Type
const GIFT_CARD_SEND_BY_EMAIL = 1;
const GIFT_CARD_SEND_BY_PHONE = 2;

const CURRENCY_TYPE_CRYPTO = 1;
const CURRENCY_TYPE_FIAT = 2;

const CONVERT_TYPE_CRYPTO_TO_FIAT = 1;
const CONVERT_TYPE_FIAT_TO_CRYPTO = 2;
const CONVERT_TYPE_FIAT_TO_FIAT = 3;

const DEFAULT_LOADING_ANNIMATIOM = 1;
const LOGO_LOADING_ANNIMATIOM = 2;

const BOT_SYNC_MARKET_PRICE = 'neutral';
const BOT_INCREASE_MARKET_PRICE = 'increase';
const BOT_DECREASE_MARKET_PRICE = 'decrease';
const BOT_RANDOM_MARKET_PRICE = 'random';

const RATE_SOURCE_DEFAULT = 'default';
const RATE_SOURCE_DB = 'rate_db';
const RATE_SOURCE_EXTERNAL = 'external';

const PERCENT_LEFT_LEN = 1;
const PERCENT_DECIMAL = 3;
const MAX_PERCENT = 10;
const MIN_PERCENT = 0.01;

const BOT_ORDER_PROCESS_REDIS=2;
const BOT_ORDER_PROCESS_DIRECT_COMMAND=1;
