console.log('farming/form.js loaded');
import Choices from "choices.js";
import {BlockchainDapp} from "../blockchain-dapp";

new Choices('#strategy_farming_coin');
BlockchainDapp('strategy_farming_blockchain', 'strategy_farming_dapp', 'id-dapp');