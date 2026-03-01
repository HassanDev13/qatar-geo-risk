import React from "react";
import {
    Radar,
    RadarChart,
    PolarGrid,
    PolarAngleAxis,
    PolarRadiusAxis,
    ResponsiveContainer,
    Tooltip,
    Legend,
} from "recharts";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { useLanguage } from "@/Contexts/LanguageContext";
import { Badge } from "@/Components/ui/badge";

const ArabicTermAgreementChart = ({ data, currentTerm }) => {
    const { t } = useLanguage();

    // If no data or empty data, show message
    if (!data || data.length === 0) {
        return (
            <Card className="w-full">
                <CardHeader>
                    <CardTitle className="text-sm sm:text-base">
                        {t("charts.arabic_term_agreement")}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="text-center p-4 text-gray-500 dark:text-gray-400">
                        <p className="text-sm">
                            {t("charts.no_agreement_data")}
                        </p>
                        <p className="text-xs mt-2">
                            {t("charts.only_one_resource_available")}
                        </p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    // Prepare radar chart data
    const radarData = data.map((item, index) => ({
        term: item.term,
        fullTerm: item.term,
        frequency: item.count,
        resourceCount: item.resources ? item.resources.length : 0,
        rank: index + 1,
    }));

    // Custom tooltip for radar chart
    const CustomTooltip = ({ active, payload, label }) => {
        if (active && payload && payload.length) {
            const dataItem = payload[0].payload;
            return (
                <div className="bg-white dark:bg-gray-800 p-3 border border-gray-200 dark:border-gray-700 rounded shadow-sm max-w-xs">
                    <div className="mb-2">
                        <p className="font-medium text-gray-900 dark:text-white text-right font-arabic" dir="rtl">
                            {dataItem.fullTerm}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {t("charts.rank")}: #{dataItem.rank}
                        </p>
                    </div>
                    <div className="space-y-1">
                        <div className="flex justify-between items-center">
                            <span className="text-sm text-gray-600 dark:text-gray-300">
                                {t("charts.frequency")}:
                            </span>
                            <span className="font-semibold text-gray-900 dark:text-white">
                                {dataItem.frequency}
                            </span>
                        </div>
                        <div className="flex justify-between items-center">
                            <span className="text-sm text-gray-600 dark:text-gray-300">
                                {t("charts.resources_using")}:
                            </span>
                            <span className="font-semibold text-gray-900 dark:text-white">
                                {dataItem.resourceCount}
                            </span>
                        </div>
                    </div>
                    {currentTerm?.term_ar === dataItem.fullTerm && (
                        <div className="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <Badge variant="default" className="text-xs">
                                {t("charts.current_term")}
                            </Badge>
                        </div>
                    )}
                </div>
            );
        }
        return null;
    };

    // Find maximum frequency for scaling
    const maxFrequency = Math.max(...radarData.map(item => item.frequency));

    return (
        <Card className="w-full">
            <CardHeader>
                <CardTitle className="text-sm sm:text-base">
                    {t("charts.arabic_term_agreement")}
                </CardTitle>
                <p className="text-xs text-gray-500 dark:text-gray-400">
                    {t("charts.agreement_description")}
                </p>
            </CardHeader>
            <CardContent>
                <div className="h-80 sm:h-96">
                    <ResponsiveContainer width="100%" height="100%">
                        <RadarChart
                            data={radarData}
                            margin={{ top: 10, right: 30, bottom: 10, left: 30 }}
                        >
                            <PolarGrid stroke="#e5e7eb" />
                            <PolarAngleAxis
                                dataKey="term"
                                tick={{ fontSize: 12 }}
                                stroke="#6b7280"
                                tickFormatter={(value) => {
                                    // Shorten Arabic terms for display
                                    if (value.length > 8) {
                                        return value.substring(0, 8) + "...";
                                    }
                                    return value;
                                }}
                            />
                            <PolarRadiusAxis
                                angle={30}
                                domain={[0, maxFrequency]}
                                stroke="#6b7280"
                                tick={{ fontSize: 10 }}
                            />
                            <Radar
                                name={t("charts.frequency")}
                                dataKey="frequency"
                                stroke="#3B82F6"
                                fill="#3B82F6"
                                fillOpacity={0.6}
                            />
                            <Radar
                                name={t("charts.resource_count")}
                                dataKey="resourceCount"
                                stroke="#10B981"
                                fill="#10B981"
                                fillOpacity={0.4}
                            />
                            <Tooltip content={<CustomTooltip />} />
                            <Legend
                                verticalAlign="bottom"
                                height={36}
                                wrapperStyle={{ fontSize: "12px" }}
                            />
                        </RadarChart>
                    </ResponsiveContainer>
                </div>

                {/* Top Arabic terms list */}
                <div className="mt-6">
                    <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        {t("charts.top_arabic_terms")}
                    </h4>
                    <div className="space-y-3">
                        {radarData.slice(0, 5).map((item, index) => (
                            <div
                                key={index}
                                className={`p-3 rounded border ${
                                    currentTerm?.term_ar === item.fullTerm
                                        ? "border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20"
                                        : "border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"
                                }`}
                            >
                                <div className="flex justify-between items-start">
                                    <div className="flex items-start space-x-3">
                                        <div className="flex-shrink-0">
                                            <div className="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <span className="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    #{item.rank}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <p className="text-sm font-arabic text-right text-gray-900 dark:text-white" dir="rtl">
                                                {item.fullTerm}
                                            </p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-gray-500 dark:text-gray-400">
                                                    {t("charts.frequency")}:{" "}
                                                    <span className="font-semibold">
                                                        {item.frequency}
                                                    </span>
                                                </span>
                                                <span className="text-xs text-gray-500 dark:text-gray-400">
                                                    {t("charts.resources")}:{" "}
                                                    <span className="font-semibold">
                                                        {item.resourceCount}
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    {currentTerm?.term_ar === item.fullTerm && (
                                        <Badge variant="default" className="text-xs">
                                            {t("charts.current")}
                                        </Badge>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Agreement statistics */}
                <div className="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.total_terms_analyzed")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white">
                            {radarData.length}
                        </p>
                    </div>
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.most_common_term")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white truncate font-arabic text-right" dir="rtl">
                            {radarData[0]?.fullTerm || "-"}
                        </p>
                    </div>
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.highest_frequency")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white">
                            {radarData[0]?.frequency || 0}
                        </p>
                    </div>
                </div>

                {/* Agreement level indicator */}
                {radarData.length > 1 && (
                    <div className="mt-4">
                        <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {t("charts.agreement_level")}
                        </h4>
                        <div className="bg-gray-100 dark:bg-gray-800 rounded-full h-4 overflow-hidden">
                            <div
                                className="bg-green-500 h-full rounded-full transition-all duration-500"
                                style={{
                                    width: `${Math.min(
                                        100,
                                        (radarData[0]?.frequency / maxFrequency) * 100
                                    )}%`,
                                }}
                            />
                        </div>
                        <div className="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span>{t("charts.low_agreement")}</span>
                            <span>{t("charts.high_agreement")}</span>
                        </div>
                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            {radarData[0]?.frequency === maxFrequency
                                ? t("charts.strong_consensus")
                                : t("charts.varying_terms")}
                        </p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
};

export default ArabicTermAgreementChart;
